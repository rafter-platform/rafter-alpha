<?php

namespace App\Rafter\Http\Controllers;

use App\Rafter\Queue\RafterJob;
use App\Rafter\Queue\RafterWorker;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Throwable;

class RafterQueueWorkerController extends Controller
{
    protected $worker;

    /**
     * Indicates if the worker is already listening for events.
     *
     * @var bool
     */
    protected static $listeningForEvents = false;

    public function __construct(RafterWorker $worker) {
        $this->worker = $worker;
    }

    /**
     * Handle an incoming queue job from Google Cloud Tasks.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request)
    {
        if (! static::$listeningForEvents) {
            $this->listenForEvents();

            static::$listeningForEvents = true;
        }

        $this->worker->setCache(app('cache')->driver());

        try {
            $payload = base64_decode($request->getContent());

            $this->worker->runRafterJob(
                $this->marshalJob($payload),
                'rafter',
                $this->gatherWorkerOptions()
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }

        return response('');
    }

    /**
     * Turn a payload and headers into an actual Job
     */
    protected function marshalJob(string $payload)
    {
        $queue = $this->worker->getManager()->connection('rafter');

        return new RafterJob(
            app(),
            $queue,
            $payload,
            request()->headers,
            $this->queueName(),
        );
    }

    /**
     * Get the name of the queue this job is associated with.
     */
    protected function queueName()
    {
        return request()->header('X-CloudTasks-QueueName');
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $delay = 0, $memory = 512,
            $timeout = 0, $sleep = 0,
            $tries = 0, $force = false,
            $stopWhenEmpty = false
        );
    }

    /**
     * Listen for the queue events in order to update the console output.
     *
     * @return void
     */
    protected function listenForEvents()
    {
        app('events')->listen(JobProcessing::class, function ($event) {
            Log::info("Job starting: {$event->job->resolveName()}");
        });

        app('events')->listen(JobProcessed::class, function ($event) {
            Log::info("Job processed: {$event->job->resolveName()}");
        });

        app('events')->listen(JobFailed::class, function ($event) {
            Log::error("Job failed: {$event->job->resolveName()}");

            $this->logFailedJob($event);
        });
    }

    /**
     * Store a failed job event.
     *
     * @param  \Illuminate\Queue\Events\JobFailed  $event
     * @return void
     */
    protected function logFailedJob(JobFailed $event)
    {
        app('queue.failer')->log(
            $event->connectionName, $event->job->getQueue(),
            $event->job->getRawBody(), $event->exception
        );
    }
}

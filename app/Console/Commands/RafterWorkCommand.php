<?php

namespace App\Console\Commands;

use App\Rafter\Queue\RafterJob;
use App\Rafter\Queue\RafterWorker;
use Illuminate\Console\Command;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class RafterWorkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rafter:work
                            {message : The Base64 encoded message payload}
                            {headers : The Base64 encoded headers from Cloud Tasks}
                            {--delay=0 : The number of seconds to delay failed jobs}
                            {--tries=0 : Number of times to attempt a job before logging it failed}
                            {--force : Force the worker to run even in maintenance mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process a Rafter job';

    /**
     * Hide from the command list
     */
    protected $hidden = true;

    /**
     * The Rafter worker instance.
     * @var \Rafter\RafterWorker
     */
    protected $worker;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(RafterWorker $worker)
    {
        parent::__construct();

        $this->worker = $worker;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->worker->setCache($this->laravel['cache']->driver());

        return $this->worker->runRafterJob(
            $this->marshalJob($this->message(), $this->headers()),
            'rafter',
            $this->gatherWorkerOptions()
        );
    }

    /**
     * Turn a payload and headers into an actual Job
     */
    protected function marshalJob(array $payload, array $headers)
    {
        $queue = $this->worker->getManager()->connection('rafter');

        return new RafterJob(
            $this->laravel,
            $queue,
            $payload,
            $headers,
            $this->queueName(),
        );
    }

    /**
     * Get the name of the queue this job is associated with.
     */
    protected function queueName()
    {

        return $this->headers()['x-cloudtasks-queuename'][0];
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        return new WorkerOptions(
            $this->option('delay'), $memory = 512,
            $timeout = 0, $sleep = 0,
            $this->option('tries'), $this->option('force'),
            $stopWhenEmpty = false
        );
    }

    /**
     * Get the decoded message payload.
     *
     * @return array
     */
    protected function message()
    {
        return tap(json_decode(base64_decode($this->argument('message')), true), function ($message) {
            if ($message === false) {
                throw new InvalidArgumentException("Unable to unserialize message.");
            }
        });
    }

    /**
     * Get the decoded headers payload.
     *
     * @return array
     */
    protected function headers()
    {
        return tap(json_decode(base64_decode($this->argument('headers')), true), function ($headers) {
            if ($headers === false) {
                throw new InvalidArgumentException("Unable to unserialize headers.");
            }
        });
    }
}

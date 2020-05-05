<?php

namespace App\Jobs;

use App\TrackedJob;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Facades\Log;
use Throwable;

trait Trackable
{
    /**
     * The model associated with the job.
     */
    public $model;

    /**
     * TrackedJob tied to this job.
     *
     * @var \App\TrackedJob
     */
    public $trackedJob;

    public function __construct($model)
    {
        $this->model = $model;

        $this->trackedJob = TrackedJob::create([
            'trackable_id' => $this->model->id,
            'trackable_type' => get_class($this->model),
            'name' => class_basename(static::class),
        ]);
    }

    /**
     * Execute the job. This is a wrapper around the #execute method,
     * which the subclass must implement.
     *
     * @return void
     */
    public function handle()
    {
        $this->trackedJob->markAsStarted();

        try {
            $response = $this->execute();

            // If the response is truthy, then we can assume
            // the trackedJob has been completed.
            if ($response) {
                $this->trackedJob->markAsFinished($response);
            }
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    /**
     * Method that a class must implement.
     *
     * @return boolean|null
     */
    abstract function execute();

    /**
     * Handle the job failing by marking the deployment as failed.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());

        $message = $exception->getMessage();

        if ($exception instanceof MaxAttemptsExceededException) {
            $message = 'This operation took too long.';
        }

        $this->trackedJob->markAsFailed($message);
    }
}

<?php

namespace App\Jobs;

use App\Deployment;
use App\PendingDeployChain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Throwable;

/**
 * A DeploymentStepJob is a wrapper around a "normal" job, but it implements
 * tracking a DeploymentStep record in conjunction with a queued job for a Deployment.
 * Its subclass should implement the `execute` method rather than the `handle` method.
 */
abstract class DeploymentStepJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The deployment associated with the job.
     *
     * @var \App\Deployment
     */
    public $deployment;

    /**
     * Deployment step tied to this job.
     *
     * @var \App\DeploymentStep
     */
    public $step;

    public function __construct(Deployment $deployment) {
        $this->deployment = $deployment;

        // When the job is instantiated, create the step record
        $this->step = $this->deployment->steps()->create([
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
        $this->step->markAsStarted();

        $response = $this->execute();

        // If the response is true, then we can assume
        // the step has been completed.
        if ($response) {
            $this->step->markAsFinished();
        }
    }

    /**
     * Method that subclass must implement
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
        $this->step->markAsFailed();
        $this->deployment->markAsFailed();
    }

    /**
     * Set the jobs that should run if this job is successful.
     *
     * @param  array  $chain
     * @return \App\PendingDeployChain
     */
    public function withDeploymentChain($chain)
    {
        return new PendingDeployChain($this, $chain);
    }
}

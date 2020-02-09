<?php

namespace App\Jobs;

use App\GoogleProject;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class WaitForProjectApisToBeEnabled implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $project;

    // 2 minutes
    public $tries = 12;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(GoogleProject $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $operation = $this->project->client()->getEnableApisOperation($this->project->operation_name);

            if ($operation->isInProgress()) {
                $this->release(10);
                return;
            }

            $this->project->setReady();
        } catch (Throwable $e) {
            $this->fail($e);
        }
    }

    public function failed(Throwable $exception)
    {
        $this->project->setFailed();
    }
}

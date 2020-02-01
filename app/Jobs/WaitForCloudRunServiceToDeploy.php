<?php

namespace App\Jobs;

use App\Deployment;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class WaitForCloudRunServiceToDeploy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deployment;

    // 1 minute
    public $tries = 6;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = $this->deployment->getCloudRunService();

        if (! $service->isReady()) {
            $this->release(10);
            return;
        }

        if ($service->hasErrors()) {
            $this->fail(new Exception($service->getError()));
            return;
        }

        // TODO: Mark deployment as successful if this is NOT a new service

        // Else, set the URL for the first time and move on.
        $this->deployment->environment->setUrl($service->getUrl());
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->deployment->markAsFailed();
    }
}

<?php

namespace App\Jobs;

use App\Deployment;
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

        // If it just started
        if (empty($service['status'])) {
            $this->release(10);
            return;
        }

        // If it's still going
        if (collect($service['status']['conditions'])->every(function ($item) { return $item['status'] !== 'True'; })) {
            $this->release(10);
            return;
        }

        // TODO: Check to see if any part of the service is failing

        // Else, everything went well.
        $this->deployment->environment->setUrl($service['metadata']['url']);
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->deployment->markAsFailed();
    }
}

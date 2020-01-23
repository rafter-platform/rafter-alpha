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

class WaitForImageToBeBuilt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deployment;

    // 20 minutes
    public $tries = 80;

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
        $operation = $this->deployment->getBuildOperation();
        $status = $operation['metadata']['build']['status'];

        if (collect(['FAILURE', 'INTERNAL_ERROR', 'TIMEOUT', 'CANCELLED'])->contains($status)) {
            $this->fail(new Exception($operation['metadata']['error']['message']));
            return;
        }

        // If it's working, check again in 15 seconds
        if (collect(['QUEUED', 'WORKING'])->contains($status)) {
            $this->release(15);
            return;
        }

        if ($status === 'SUCCESS') {
            $this->deployment->recordBuiltImage($operation['metadata']['build']['id']);
        }
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->deployment->markAsFailed();
    }
}

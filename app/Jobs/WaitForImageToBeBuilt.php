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

        if ($operation->isDone() && $operation->hasError()) {
            $this->fail(new Exception($operation->errorMessage()));
            return;
        }

        // If it's working, check again in 15 seconds
        if (! $operation->isDone()) {
            $this->release(15);
            return;
        }

        $this->deployment->recordBuiltImage($operation->builtImage());
    }

    public function failed(Throwable $exception)
    {
        Log::error($exception->getMessage());
        $this->deployment->markAsFailed();
    }
}

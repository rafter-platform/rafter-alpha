<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitForImageToBeBuilt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    // 20 minutes
    public $tries = 80;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $operation = $this->model->getBuildOperation();

        if ($operation->isDone() && $operation->hasError()) {
            $this->fail(new Exception($operation->errorMessage()));
            return;
        }

        // If it's working, check again in 15 seconds
        if (!$operation->isDone()) {
            $this->release(15);
            return;
        }

        $this->model->recordBuiltImage($operation->builtImage());

        return true;
    }
}

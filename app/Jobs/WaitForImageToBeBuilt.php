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

    public function handle()
    {
        $operation = $this->model->getBuildOperation();

        if ($operation->isDone() && $operation->hasError()) {
            $this->fail(new Exception($operation->errorMessage()));
            return;
        }

        if (!$operation->isDone()) {
            $message = sprintf(
                'Image is being built. <a href="%s" target="_blank">View the output in Cloud Build</a>.',
                $operation->getUrl()
            );

            $this->trackedJob->setOutput($message);
            $this->release(15);
            return;
        }

        $this->model->recordBuiltImage($operation->builtImage());

        return sprintf(
            'Image built successfully! <a href="%s" target="_blank">View the output in Cloud Build</a>.',
            $operation->getUrl()
        );
    }

    public function appendToFailureOutput()
    {
        $operation = $this->model->getBuildOperation();

        return sprintf(
            '<a href="%s" target="_blank">View the output in Cloud Build</a>.',
            $operation->getUrl()
        );
    }
}

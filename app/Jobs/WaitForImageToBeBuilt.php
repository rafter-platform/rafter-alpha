<?php

namespace App\Jobs;

use Exception;

class WaitForImageToBeBuilt extends DeploymentStepJob
{
    // 20 minutes
    public $tries = 80;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
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

        return true;
    }
}

<?php

namespace App\Jobs;

use App\GoogleCloud\CloudBuildConfig;
use Exception;

class CreateImageForDeployment extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $build = new CloudBuildConfig($this->deployment);

            $operation = $this->deployment->submitBuild($build);

            $this->deployment->markAsInProgress();
            $this->deployment->update(['operation_name' => $operation['name']]);

            return true;
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}

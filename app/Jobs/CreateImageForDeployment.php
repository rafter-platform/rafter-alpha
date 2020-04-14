<?php

namespace App\Jobs;

use App\GoogleCloud\CloudBuildConfig;

class CreateImageForDeployment extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $build = new CloudBuildConfig($this->deployment);

        $operation = $this->deployment->submitBuild($build);

        $this->deployment->update(['operation_name' => $operation['name']]);

        return true;
    }
}

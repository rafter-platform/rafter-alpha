<?php

namespace App\Jobs;

class CreateCloudRunService extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $this->deployment->createCloudRunService();
        $this->deployment->createCloudRunWorkerService();

        return true;
    }
}

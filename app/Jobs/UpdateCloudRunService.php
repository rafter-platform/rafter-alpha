<?php

namespace App\Jobs;

class UpdateCloudRunService extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $this->deployment->updateCloudRunService();
        $this->deployment->updateCloudRunWorkerService();

        return true;
    }
}

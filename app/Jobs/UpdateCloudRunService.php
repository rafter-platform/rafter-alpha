<?php

namespace App\Jobs;

use Throwable;

class UpdateCloudRunService extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->deployment->updateCloudRunService();
            $this->deployment->updateCloudRunWorkerService();

            return true;
        } catch (Throwable $exception) {
            $this->fail($exception);
        }
    }
}

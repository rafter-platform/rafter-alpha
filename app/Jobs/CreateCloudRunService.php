<?php

namespace App\Jobs;

use Throwable;

class CreateCloudRunService extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->deployment->createCloudRunService();

            return true;
        } catch (Throwable $exception) {
            $this->fail($exception);
        }
    }
}

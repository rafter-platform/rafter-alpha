<?php

namespace App\Jobs;

use Exception;

class WaitForCloudRunServiceToDeploy extends DeploymentStepJob
{
    // 1 minute
    public $tries = 6;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $service = $this->deployment->getCloudRunService();

        if (! $service->isReady() && ! $service->hasErrors()) {
            $this->release(10);
            return;
        }

        if ($service->hasErrors()) {
            $this->fail(new Exception($service->getError()));
            return;
        }

        // TODO: Mark deployment as successful if this is NOT a new service

        // Else, set the URL for the first time and move on.
        $this->deployment->environment->setUrl($service->getUrl());

        return true;
    }
}

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
        $webService = $this->deployment->getCloudRunWebService();
        $workerService = $this->deployment->getCloudRunWorkerService();

        // Check both services to see if they're ready. every() will eagerly stop
        // if the first service isn't ready, so the same job isn't released into the queue
        // multiple times.
        $ready = collect([$webService, $workerService])->every(function ($service) {
            if (! $service->isReady() && ! $service->hasErrors()) {
                $this->release(10);
                return false;
            }

            if ($service->hasErrors()) {
                $this->fail(new Exception($service->getError()));
                return false;
            }

            return true;
        });

        if (! $ready) {
            return;
        }

        $this->deployment->environment->setUrl($webService->getUrl());
        $this->deployment->environment->setWorkerUrl($workerService->getUrl());

        return true;
    }
}

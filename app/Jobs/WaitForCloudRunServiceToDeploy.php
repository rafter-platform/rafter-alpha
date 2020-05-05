<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitForCloudRunServiceToDeploy implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    // 1 minute
    public $tries = 6;

    public function handle()
    {
        $webService = $this->model->getCloudRunWebService();
        $workerService = $this->model->getCloudRunWorkerService();

        // Check both services to see if they're ready. every() will eagerly stop
        // if the first service isn't ready, so the same job isn't released into the queue
        // multiple times.
        $ready = collect([$webService, $workerService])->every(function ($service) {
            if (!$service->hasStatus()) {
                $this->release(10);
                return false;
            }

            if (!$service->isReady() && !$service->hasErrors()) {
                $this->release(10);
                return false;
            }

            if ($service->hasErrors()) {
                $this->fail(new Exception($service->getError()));
                return false;
            }

            return true;
        });

        if (!$ready) {
            return;
        }

        $this->model->environment->setUrl($webService->getUrl());
        $this->model->environment->setWorkerUrl($workerService->getUrl());

        return true;
    }
}

<?php

namespace App\Jobs;

use App\GoogleCloud\CloudRunConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateCloudRunService implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        try {
            $this->model->createCloudRunService();
            $this->model->createCloudRunWorkerService();
        } catch (RequestException $e) {
            $body = $e->response->json();

            if ($body['error']['status'] == 'ALREADY_EXISTS') {
                $config = new CloudRunConfig($this->model);

                $this->model->environment->setWebName($config->name());
                $this->model->environment->setWorkerName($config->forWorker()->name());

                $this->model->updateCloudRunService();
                $this->model->updateCloudRunWorkerService();

                return "Existing Cloud Run services detected. Updating those services with the latest image instead.";
            }

            throw $e;
        }

        return true;
    }
}

<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SetBuildSecrets implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        $token = $this->model->sourceProvider()->token();

        $enviroment = $this->model->environment;
        $secretName = $enviroment->gitTokenSecretName();

        $this->model->environment->setSecret($secretName, $token);

        return true;
    }
}

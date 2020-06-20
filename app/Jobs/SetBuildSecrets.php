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
        $environment = $this->model->environment;

        $secrets = $environment->buildSecrets()->get();

        $secrets->each(function ($secret) {
            $this->model->environment->setSecret($secret['name'], $secret['value']);
        });

        return true;
    }
}

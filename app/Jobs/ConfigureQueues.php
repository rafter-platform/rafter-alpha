<?php

namespace App\Jobs;

use App\GoogleCloud\QueueConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConfigureQueues implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        $queueConfig = new QueueConfig($this->model->environment);

        $this->model->environment->client()->createOrUpdateQueue($queueConfig);

        return true;
    }
}

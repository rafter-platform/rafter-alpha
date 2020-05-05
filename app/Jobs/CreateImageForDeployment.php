<?php

namespace App\Jobs;

use App\GoogleCloud\CloudBuildConfig;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateImageForDeployment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        $build = new CloudBuildConfig($this->model);

        $operation = $this->model->submitBuild($build);

        $this->model->update(['operation_name' => $operation['name']]);

        return true;
    }
}

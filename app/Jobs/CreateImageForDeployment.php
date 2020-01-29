<?php

namespace App\Jobs;

use App\Deployment;
use App\GoogleCloud\CloudBuildConfig;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateImageForDeployment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deployment;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $build = new CloudBuildConfig($this->deployment);

        $operation = $this->deployment->submitBuild($build);

        $this->deployment->markAsInProgress();
        $this->deployment->update(['operation_name' => $operation['name']]);
    }
}

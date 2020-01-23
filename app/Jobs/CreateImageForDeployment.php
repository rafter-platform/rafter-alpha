<?php

namespace App\Jobs;

use App\CloudBuild;
use App\Deployment;
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
        // TODO: Use either GitHub event or manual push payload URL
        $build = (new CloudBuild($this->deployment->project))
            ->forManualPush('rafter-demo-project-rafter-uploads', 'rafter-demo.tar.gz');

        $operation = $this->deployment->submitBuild($build);

        $this->deployment->markAsInProgress();
        $this->deployment->update(['operation_name' => $operation['name']]);
    }
}

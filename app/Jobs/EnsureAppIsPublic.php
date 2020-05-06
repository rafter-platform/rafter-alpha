<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EnsureAppIsPublic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Trackable;

    public function handle()
    {
        $environment = $this->model->environment;

        // Get the existing policies
        $policy = $environment->client()->getIamPolicyForCloudRunService($environment);
        $policy->setPublic();

        // Update the policy
        $environment->client()->setIamPolicyForCloudRunService($environment, $policy->getPolicy());

        return true;
    }
}

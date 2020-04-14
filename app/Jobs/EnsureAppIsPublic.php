<?php

namespace App\Jobs;

class EnsureAppIsPublic extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        $environment = $this->deployment->environment;

        // Get the existing policies
        $policy = $environment->client()->getIamPolicyForCloudRunService($environment);

        if (! $policy->isPublic()) {
            $policy->setPublic();
        }

        // Update the policy
        $environment->client()->setIamPolicyForCloudRunService($environment, $policy->getPolicy());

        return true;
    }
}

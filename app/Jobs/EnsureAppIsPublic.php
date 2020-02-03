<?php

namespace App\Jobs;

use Exception;

class EnsureAppIsPublic extends DeploymentStepJob
{
    /**
     * Execute the job.
     *
     * @return void
     */
    public function execute()
    {
        try {
            $environment = $this->deployment->environment;

            // Get the existing policies
            $policy = $environment->client()->getIamPolicyForCloudRunService($environment);

            if (! $policy->isPublic()) {
                $policy->setPublic();
            }

            // Update the policy
            $environment->client()->setIamPolicyForCloudRunService($environment, $policy->getPolicy());

            return true;
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}

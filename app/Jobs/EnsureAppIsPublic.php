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

            // Add the invoker role to allUsers (public, anon)
            $policy['bindings'][] = [
                'role' => 'roles/run.invoker',
                'members' => [
                    'allUsers',
                ],
            ];

            // Update the policy
            $environment->client()->setIamPolicyForCloudRunService($environment, $policy);

            // Assuming nothing went wrong, we are good.
            return true;
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}

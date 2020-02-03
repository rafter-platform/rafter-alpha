<?php

namespace App\Jobs;

class FinalizeDeployment extends DeploymentStepJob
{
    public function execute()
    {
        $this->deployment->markAsSuccessful();

        return true;
    }
}

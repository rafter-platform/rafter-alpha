<?php

namespace App\Jobs;

class StartDeployment extends DeploymentStepJob
{
    public function execute()
    {
        $this->deployment->markAsInProgress();

        return true;
    }
}

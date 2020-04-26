<?php

namespace App\Jobs;

class FinalizeDeployment extends DeploymentStepJob
{
    public function execute()
    {
        $this->deployment->markAsSuccessful();

        $this->environment->activeDeployment()->associate($this->deployment);
        $this->environment->save();

        return true;
    }
}

<?php

namespace App\Jobs;

class StartScheduler extends DeploymentStepJob
{
    public function execute()
    {
        $this->environment->startScheduler();
    }
}

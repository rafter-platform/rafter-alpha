<?php

namespace App\Jobs;

use App\GoogleCloud\QueueConfig;

class ConfigureQueues extends DeploymentStepJob
{
    public function execute()
    {
        $queueConfig = new QueueConfig($this->deployment->environment);

        $this->deployment->environment->client()->createOrUpdateQueue($queueConfig);

        return true;
    }
}

<?php

namespace App\Jobs;

use App\GoogleCloud\QueueConfig;
use Exception;

class ConfigureQueues extends DeploymentStepJob
{
    public function execute()
    {
        try {
            $queueConfig = new QueueConfig($this->deployment->environment);

            $this->deployment->environment->client()->createOrUpdateQueue($queueConfig);

            return true;
        } catch (Exception $e) {
            $this->fail($e);
        }
    }
}

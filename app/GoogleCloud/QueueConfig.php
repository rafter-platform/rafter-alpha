<?php

namespace App\GoogleCloud;

use App\Environment;

class QueueConfig
{
    protected $environment;

    public function __construct(Environment $environment) {
        $this->environment = $environment;
    }

    /**
     * The name of the queue
     *
     * @return string
     */
    public function name()
    {
        return sprintf(
            "projects/%s/locations/%s/queues/%s",
            $this->environment->projectId(),
            $this->environment->project->region,
            $this->environment->queueName()
        );
    }

    public function config()
    {
        return [
            'name' => $this->name(),
        ];
    }
}

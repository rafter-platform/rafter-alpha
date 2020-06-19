<?php

namespace App\GoogleCloud;

use App\Deployment;

class CloudRunConfig
{
    protected $deployment;
    protected $environment;

    /**
     * Whether this service is a worker/non-web service
     *
     * @var boolean
     */
    protected $isWorker = false;

    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
        $this->environment = $deployment->environment;
    }

    /**
     * Mark this service as a worker service
     *
     * @return self
     */
    public function forWorker()
    {
        $this->isWorker = true;

        return $this;
    }

    protected function getEnvironmentOption($key)
    {
        $prefix = $this->isWorker ? 'worker' : 'web';

        return $this->environment->getOption($prefix . '_' . $key);
    }

    /**
     * Get the name of the service
     *
     * @return string
     */
    public function name()
    {
        $base = $this->environment->slug();

        if ($this->isWorker) {
            return $base . '-worker';
        }

        return $base;
    }

    public function projectId()
    {
        return $this->environment->project->googleProject->project_id;
    }

    public function region()
    {
        return $this->environment->project->region;
    }

    public function metadata()
    {
        return [
            'name' => $this->name(),
            'namespace' => $this->projectId(),
        ];
    }

    /**
     * @see https://cloud.google.com/run/docs/reference/rest/v1/RevisionTemplate
     */
    public function revisionMetadata()
    {
        $annotations = [
            'autoscaling.knative.dev/maxScale' => (string) $this->getEnvironmentOption('max_instances'),
        ];

        if ($this->environment->usesDatabase()) {
            $connectionString = $this->environment->database->databaseInstance->connectionString();
            $annotations['run.googleapis.com/cloudsql-instances'] = $connectionString;
        }

        return [
            'annotations' => $annotations,
        ];
    }

    public function image()
    {
        return $this->deployment->image;
    }

    /**
     * Get environmental variables to set.
     *
     * @return array
     */
    public function env()
    {
        $vars = $this->deployment->envVars();

        if ($this->isWorker) {
            $vars->set('IS_RAFTER_WORKER', 'true');
        }

        return $vars->all();
    }

    /**
     * Define the system resources (Memory and CPU) for this service.
     *
     * @return array
     */
    public function resources()
    {
        return [
            'limits' => [
                'memory' => (string) $this->getEnvironmentOption('memory'),
                'cpu' => (string) $this->getEnvironmentOption('cpu'),
            ],
        ];
    }

    public function container()
    {
        return [
            'image' => $this->image(),
            'env' => $this->env(),
            'resources' => $this->resources(),
        ];
    }

    public function spec()
    {
        return [
            'template' => array_filter([
                'metadata' => $this->revisionMetadata(),
                'spec' => [
                    'timeoutSeconds' => $this->getEnvironmentOption('request_timeout'),
                    'containerConcurrency' => $this->getEnvironmentOption('max_requests_per_container'),
                    'containers' => [
                        $this->container(),
                    ]
                ]
            ]),
        ];
    }

    public function config()
    {
        return [
            'apiVersion' => 'serving.knative.dev/v1',
            'kind' => 'Service',
            'metadata' => $this->metadata(),
            'spec' => $this->spec(),
        ];
    }
}

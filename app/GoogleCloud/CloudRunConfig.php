<?php

namespace App\GoogleCloud;

use App\Deployment;

class CloudRunConfig
{
    protected $deployment;
    protected $environment;

    public function __construct(Deployment $deployment) {
        $this->deployment = $deployment;
        $this->environment = $deployment->environment;
    }

    public function name()
    {
        return $this->environment->slug();
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
        if ($this->environment->usesDatabase()) {
            $connectionString = $this->environment->database->databaseInstance->connectionString();

            return [
                'annotations' => [
                    'run.googleapis.com/cloudsql-instances' => $connectionString,
                ],
            ];
        }

        return [];
    }

    public function image()
    {
        return $this->deployment->image;
    }

    public function env()
    {
        // TODO: Inject system-provided env vars based on database, queue, etc setup
        // TODO: Merge environment-specific env vars with system-provided vars
        return [
            [
                'name' => 'DB_CONNECTION',
                'value' => 'sqlite'
            ],
            [
                'name' => 'DB_DATABASE',
                'value' => '/var/www/database/database.sqlite'
            ],
        ];
    }

    public function container()
    {
        return [
            'image' => $this->image(),
            'env' => $this->env(),
        ];
    }

    public function spec()
    {
        return [
            'template' => array_filter([
                'metadata' => $this->revisionMetadata(),
                'spec' => [
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

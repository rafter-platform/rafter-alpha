<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'operation_name',
        'status',
        'image',
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    /**
     * Mark a deployment as in progress
     */
    public function markAsInProgress()
    {
        $this->update(['status' => static::STATUS_IN_PROGRESS]);
    }

    /**
     * Mark a deployment as failed
     */
    public function markAsFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
    }

    /**
     * Mark a deployment as successful
     */
    public function markAsSuccessful()
    {
        $this->update(['status' => static::STATUS_SUCCESSFUL]);
    }

    /**
     * Create a CloudBuild using a configuration.
     */
    public function submitBuild(CloudBuild $cloudBuild)
    {
        return $this->project->googleProject->client()->createImageForBuild($cloudBuild);
    }

    /**
     * Get the Build operation from CloudBuild
     */
    public function getBuildOperation()
    {
        return $this->project->googleProject->client()->getCloudBuildOperation($this->operation_name);
    }

    /**
     * Record the built image from CloudBuild.
     */
    public function recordBuiltImage($buildId)
    {
        $build = $this->project->googleProject->client()->getBuild($buildId);

        $image = sprintf(
            "%s@%s",
            $build['results']['images'][0]['name'],
            $build['results']['images'][0]['digest']
        );

        $this->update(['image' => $image]);
    }

    public function createCloudRunService()
    {
        $service = [
            'apiVersion' => 'serving.knative.dev/v1',
            'kind' => 'Service',
            'metadata' => [
                'name' => $this->project->slug(),
                'namespace' => $this->project->googleProject->project_id,
            ],
            'spec' => [
                'template' => [
                    'spec' => [
                        'containers' => [
                            [
                                'image' => $this->image,
                                'env' => [
                                    [
                                        'name' => 'DB_CONNECTION',
                                        'value' => 'sqlite'
                                    ],
                                    [
                                        'name' => 'DB_DATABASE',
                                        'value' => '/var/www/database/database.sqlite'
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $region = $this->project->region;
        $response = $this->project->googleProject->client()->createCloudRunService($service, $region);

        dump($response);
    }
}

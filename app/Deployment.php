<?php

namespace App;

use App\GoogleCloud\CloudBuildConfig;
use App\GoogleCloud\CloudRunConfig;
use App\Services\GoogleApi;
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

    public function environment()
    {
        return $this->belongsTo('App\Environment');
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
    public function submitBuild(CloudBuildConfig $cloudBuild)
    {
        return $this->client()->createImageForBuild($cloudBuild);
    }

    /**
     * Get the Build operation from CloudBuild
     */
    public function getBuildOperation()
    {
        return $this->client()->getCloudBuildOperation($this->operation_name);
    }

    /**
     * Get Cloud Run service
     */
    public function getCloudRunService()
    {
        return $this->client()->getCloudRunService($this->environment->slug(), $this->environment->project->region);
    }

    /**
     * Record the built image from CloudBuild.
     */
    public function recordBuiltImage($buildId)
    {
        $build = $this->client()->getBuild($buildId);

        $image = sprintf(
            "%s@%s",
            $build['results']['images'][0]['name'],
            $build['results']['images'][0]['digest']
        );

        $this->update(['image' => $image]);
    }

    public function createCloudRunService()
    {
        $cloudRunConfig = new CloudRunConfig($this);

        $this->client()->createCloudRunService($cloudRunConfig);
    }

    public function updateCloudRunService()
    {
        $cloudRunConfig = new CloudRunConfig($this);

        $this->client()->replaceCloudRunService($cloudRunConfig);
    }

    public function client(): GoogleApi
    {
        return $this->environment->client();
    }
}

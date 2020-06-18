<?php

namespace App;

use App\Casts\Options;
use App\GoogleCloud\CloudBuildConfig;
use App\GoogleCloud\CloudRunConfig;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    const STATUS_QUEUED = 'queued';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_SUCCESSFUL = 'successful';
    const STATUS_FAILED = 'failed';

    protected $casts = [
        'meta' => Options::class,
    ];

    protected $guarded = [];

    public function environment()
    {
        return $this->belongsTo('App\Environment');
    }

    public function steps()
    {
        return $this->morphMany('App\TrackedJob', 'trackable');
    }

    public function project()
    {
        return $this->environment->project;
    }

    public function sourceProvider()
    {
        return $this->project()->sourceProvider;
    }

    public function initiator()
    {
        return $this->belongsTo('App\User', 'initiator_id')->withDefault([
            'name' => 'Anonymous User',
        ]);
    }

    /**
     * The git repo for this deployment
     *
     * @return string
     */
    public function repository(): string
    {
        return $this->project()->repository;
    }

    /**
     * Get the tarball URL for the deployment.
     *
     * @return string
     */
    public function tarballUrl()
    {
        return $this->sourceProvider()->client()->tarballUrl($this);
    }

    /**
     * Get the Git clone URL with a token.
     *
     * @return string
     */
    public function cloneUrl()
    {
        return $this->sourceProvider()->client()->cloneUrl($this);
    }

    /**
     * Mark a deployment as in progress
     */
    public function markAsInProgress()
    {
        $this->update(['status' => static::STATUS_IN_PROGRESS]);
    }

    /**
     * Whether the deployment is in progress.
     *
     * @return boolean
     */
    public function isInProgress(): bool
    {
        return $this->status == static::STATUS_IN_PROGRESS || $this->status == static::STATUS_QUEUED;
    }

    /**
     * Mark a deployment as failed
     */
    public function markAsFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
        $this->sourceProvider()->updateDeploymentStatus($this, 'failure');
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
     * Get Cloud Run web service
     */
    public function getCloudRunWebService()
    {
        return $this->client()->getCloudRunService($this->environment->web_service_name, $this->environment->project->region);
    }

    /**
     * Get Cloud Run web service
     */
    public function getCloudRunWorkerService()
    {
        return $this->client()->getCloudRunService($this->environment->worker_service_name, $this->environment->project->region);
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

    /**
     * Create a cloud run service for this deployment,
     * and set the name on the environment
     *
     * @return void
     */
    public function createCloudRunService()
    {
        $cloudRunConfig = new CloudRunConfig($this);

        $this->client()->createCloudRunService($cloudRunConfig);
        $this->environment->setWebName($cloudRunConfig->name());
    }

    /**
     * Create a Cloud run worker service for this deployment,
     * and set the name on the environment
     *
     * @return void
     */
    public function createCloudRunWorkerService()
    {
        $cloudRunConfig = (new CloudRunConfig($this))
            ->forWorker();

        $this->client()->createCloudRunService($cloudRunConfig);
        $this->environment->setWorkerName($cloudRunConfig->name());
    }

    /**
     * Update the Cloud Run service with this deployment
     *
     * @return void
     */
    public function updateCloudRunService()
    {
        $cloudRunConfig = new CloudRunConfig($this);

        $this->client()->replaceCloudRunService($cloudRunConfig);
    }

    /**
     * Update a Cloud run worker service for this deployment.
     *
     * @return void
     */
    public function updateCloudRunWorkerService()
    {
        $cloudRunConfig = (new CloudRunConfig($this))
            ->forWorker();

        $this->client()->replaceCloudRunService($cloudRunConfig);
    }

    /**
     * When attempting to create a new Cloud Run service, and one already exists,
     * we want to ensure that we set the web and worker service names properly so
     * the service can be updated as expected. Further, we import the env vars from
     * the existing service to ensure we don't lose them in the next deploy.
     *
     * @return void
     */
    public function importExistingCloudRunService()
    {
        $config = new CloudRunConfig($this);

        $this->environment->setWebName($config->name());
        $this->environment->setWorkerName($config->forWorker()->name());

        $service = $this->getCloudRunWebService();
        $envVars = new EnvVars($service->envVars());

        $this->environment->update(['environmental_variables' => $envVars->toString()]);
    }

    /**
     * Redeploy a given deployment
     *
     * @param int|null $initiatorId
     * @return Deployment
     */
    public function redeploy($initiatorId = null)
    {
        return $this->environment->redeploy(
            $this,
            $initiatorId
        );
    }

    /**
     * Get the environment variables for this deployment.
     *
     * @return EnvVars
     */
    public function envVars(): EnvVars
    {
        $vars = EnvVars::fromString($this->environment->environmental_variables);

        $vars->set('IS_RAFTER', 'true');
        $vars->set('RAFTER_WORKER_URL', $this->environment->worker_url);

        if ($this->project()->isLaravel()) {
            $vars->inject([
                'RAFTER_QUEUE' => $this->environment->queueName(),
                'RAFTER_PROJECT_ID' => $this->environment->projectId(),
                'RAFTER_REGION' => $this->project()->region,
                'CACHE_DRIVER' => 'file',
                'QUEUE_CONNECTION' => 'rafter',
                'SESSION_DRIVER' => 'cookie',
                'LOG_CHANNEL' => 'syslog',
            ]);

            if ($this->environment->usesDatabase()) {
                $database = $this->environment->database;

                $vars->inject([
                    'DB_DATABASE' => $database->name,
                    'DB_USERNAME' => $database->databaseUser(),
                    'DB_PASSWORD' => $database->databasePassword(),
                    'DB_SOCKET' => "/cloudsql/{$database->connectionString()}"
                ]);
            }
        }

        return $vars;
    }

    public function createSourceProviderDeployment()
    {
        $this->sourceProvider()->createDeployment($this);
    }

    public function updateSourceProviderDeploymentStatus(string $state)
    {
        $this->sourceProvider()->updateDeploymentStatus($this, $state);
    }

    public function getRoute(): string
    {
        return route('projects.environments.deployments.show', [$this->project(), $this->environment, $this]);
    }

    /**
     * Get the duration of the deployment
     *
     * @return string
     */
    public function duration(): string
    {
        return $this->updated_at
            ->shortAbsoluteDiffForHumans($this->created_at, [
                'short' => true,
                'absolute' => true,
            ]);
    }

    public function client(): GoogleApi
    {
        return $this->environment->client();
    }
}

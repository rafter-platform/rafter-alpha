<?php

namespace App;

use App\GoogleCloud\SchedulerJobConfig;
use App\Jobs\ConfigureQueues;
use App\Jobs\CreateCloudRunService;
use App\Jobs\CreateImageForDeployment;
use App\Jobs\EnsureAppIsPublic;
use App\Jobs\FinalizeDeployment;
use App\Jobs\StartDeployment;
use App\Jobs\StartScheduler;
use App\Jobs\UpdateCloudRunService;
use App\Jobs\UpdateCloudRunServiceWithUrls;
use App\Jobs\WaitForCloudRunServiceToDeploy;
use App\Jobs\WaitForImageToBeBuilt;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class Environment extends Model
{
    const INITIAL_ENVIRONMENTS = [
        'production',
    ];

    protected $fillable = [
        'name',
        'url',
        'environmental_variables',
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function deployments()
    {
        return $this->hasMany('App\Deployment')->latest('id');
    }

    public function database()
    {
        return $this->belongsTo('App\Database');
    }

    public function sourceProvider()
    {
        return $this->project->sourceProvider;
    }

    /**
     * Get the active deployment
     *
     * @return Deployment
     */
    public function activeDeployment()
    {
        return $this->belongsTo('App\Deployment', 'active_deployment_id');
    }

    /**
     * Whether the environment is using a database.
     */
    public function usesDatabase()
    {
        return $this->database()->exists();
    }

    /**
     * Create a database for this environment on a given DatabaseInstance.
     */
    public function createDatabase(DatabaseInstance $databaseInstance)
    {
        $database = $databaseInstance->databases()->create([
            'name' => $this->slug(),
        ]);

        $this->database()->associate($database);
        $this->save();

        $database->provision();
    }

    /**
     * Get a slug version of the environment name.
     */
    public function slug()
    {
        return Str::slug($this->project->name . '-' . $this->name);
    }

    /**
     * The queue name is the slug.
     *
     * @return string
     */
    public function queueName()
    {
        return $this->slug();
    }

    /**
     * Get the GooglE Project ID
     *
     * @return string
     */
    public function projectId()
    {
        return $this->project->googleProject->project_id;
    }

    /**
     * Get the Service Account Email to be used for interactions with the API.
     *
     * @return string
     */
    public function serviceAccountEmail(): string
    {
        return $this->project->googleProject->service_account_json['client_email'];
    }

    /**
     * The region/location used for the given environment.
     *
     * @return string
     */
    public function region(): string
    {
        return $this->project->region;
    }

    /**
     * Whether this environment has been successfully deployed at least once.
     *
     * @return boolean
     */
    public function hasBeenDeployedSuccessfully()
    {
        return $this->activeDeployment()->exists();
    }

    /**
     * Provision an environment for the first time.
     *
     * @return void
     */
    public function provision()
    {
        $this->setInitialEnvironmentVariables();
        $this->createInitialDeployment();
    }

    /**
     * Set the initial environment variables for this project.
     *
     * While Rafter also injects "hidden" variables at runtime, these variables are
     * set once and not changed by Rafter. This also allows the user to modify them, e.g.
     * if they'd like to rotate the keys or change the name of their app.
     *
     * @return void
     */
    public function setInitialEnvironmentVariables()
    {
        $vars = new EnvVars();

        if ($this->project->isLaravel()) {
            $appKey = 'base64:' . base64_encode(Encrypter::generateKey(config('app.cipher')));

            $vars->inject([
                'APP_NAME' => $this->project->name,
                'APP_ENV' => $this->name,
                'APP_KEY' => $appKey,
            ]);
        }

        $this->environmental_variables = $vars->toString();
        $this->save();
    }

    /**
     * Add a single env var to this environment's variables
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function addEnvVar($key, $value)
    {
        $vars = EnvVars::fromString($this->environmental_variables);

        $vars->set($key, $value);

        $this->environmental_variables = $vars->toString();
        $this->save();
    }

    /**
     * Create an initial deployment on Cloud Run.
     */
    public function createInitialDeployment()
    {
        // TODO: Make more flexible (support manual pushes, etc)
        $deployment = $this->deployments()->create([
            'commit_hash' => $this->sourceProvider()->client()->latestHashFor($this->project->repository, $this->branch),
            'commit_message' => 'Initial Deploy',
            'initiator_id' => $this->project->team->owner->id,
        ]);

        // TODO: Make this a responsibility of... something else?
        // especially the per-project-type stuff
        $jobs = [
            new StartDeployment($deployment),
            new CreateImageForDeployment($deployment),
            new ConfigureQueues($deployment),
            new WaitForImageToBeBuilt($deployment),
            new CreateCloudRunService($deployment),
            new WaitForCloudRunServiceToDeploy($deployment),
            // // Deploy the service another time, since we now have URL env vars set
            new UpdateCloudRunServiceWithUrls($deployment),
            new WaitForCloudRunServiceToDeploy($deployment),
            new EnsureAppIsPublic($deployment),
            $this->project->isLaravel() ? new StartScheduler($deployment) : false,
            new FinalizeDeployment($deployment),
        ];

        Bus::dispatchChain(array_filter($jobs));

        return $deployment;
    }

    /**
     * Create a new deployment on Cloud Run.
     */
    public function deploy($commitHash, $commitMessage, $initiatorId)
    {
        $deployment = $this->deployments()->create([
            'commit_hash' => $commitHash,
            'commit_message' => $commitMessage,
            'initiator_id' => $initiatorId,
        ]);

        (new StartDeployment($deployment))->withDeploymentChain([
            new CreateImageForDeployment($deployment),
            new ConfigureQueues($deployment),
            new WaitForImageToBeBuilt($deployment),
            new UpdateCloudRunService($deployment),
            new WaitForCloudRunServiceToDeploy($deployment),
            new FinalizeDeployment($deployment),
        ])->dispatch();

        return $deployment;
    }

    /**
     * Redeploy a deployment without having to wait for a build.
     *
     * @param Deployment $deployment
     * @param int|null $initiatorId
     * @return Deployment
     */
    public function redeploy(Deployment $deployment, $initiatorId)
    {
        $deployment = $this->deployments()->create([
            'commit_hash' => $deployment->commit_hash,
            'commit_message' => $deployment->commit_message,
            'image' => $deployment->image,
            'initiator_id' => $initiatorId,
        ]);

        if ($this->hasBeenDeployedSuccessfully()) {
            Bus::dispatchChain(
                new StartDeployment($deployment),
                new ConfigureQueues($deployment),
                new UpdateCloudRunService($deployment),
                new WaitForCloudRunServiceToDeploy($deployment),
                new FinalizeDeployment($deployment),
            );
        } else {
            // TODO: Make this a responsibility of... something else?
            // especially the per-project-type stuff
            $jobs = [
                new StartDeployment($deployment),
                new CreateImageForDeployment($deployment),
                new ConfigureQueues($deployment),
                new WaitForImageToBeBuilt($deployment),
                new CreateCloudRunService($deployment),
                new WaitForCloudRunServiceToDeploy($deployment),
                // // Deploy the service another time, since we now have URL env vars set
                new UpdateCloudRunServiceWithUrls($deployment),
                new WaitForCloudRunServiceToDeploy($deployment),
                new EnsureAppIsPublic($deployment),
                $this->project->isLaravel() ? new StartScheduler($deployment) : false,
                new FinalizeDeployment($deployment),
            ];

            Bus::dispatchChain(array_filter($jobs));
        }

        return $deployment;
    }

    /**
     * Update the URL on the environment. This will only run once.
     */
    public function setUrl($url)
    {
        if (!empty($this->url)) return;

        $this->url = $url;
        $this->save();

        $this->addEnvVar('APP_URL', $this->url);
    }

    /**
     * Set the URL for the worker service. This will only run once.
     *
     * @param string $url
     * @return void
     */
    public function setWorkerUrl($url)
    {
        if (!empty($this->worker_url)) return;

        $this->worker_url = $url;
        $this->save();
    }

    /**
     * Set the name of the web service
     *
     * @param string $name
     * @return void
     */
    public function setWebName($name)
    {
        $this->web_service_name = $name;
        $this->save();
    }

    /**
     * Set the name of the worker service
     *
     * @param string $name
     * @return void
     */
    public function setWorkerName($name)
    {
        $this->worker_service_name = $name;
        $this->save();
    }

    /**
     * Start the scheduler job for every minute, on the minute.
     *
     * @return array
     */
    public function startScheduler()
    {
        return $this->client()->createSchedulerJob(new SchedulerJobConfig($this));
    }

    /**
     * Get logs for the service.
     *
     * @return array
     */
    public function logs($serviceName = 'web', $logType = 'all'): array
    {
        $serviceNameProperty = "{$serviceName}_service_name";

        $config = [
            'projectId' => $this->projectId(),
            'serviceName' => $this->$serviceNameProperty,
            'location' => $this->region(),
            'logType' => $logType,
        ];

        return $this->client()->getLogsForService($config);
    }

    public function client(): GoogleApi
    {
        return $this->project->googleProject->client();
    }
}

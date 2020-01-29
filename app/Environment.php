<?php

namespace App;

use App\Jobs\CreateCloudRunService;
use App\Jobs\CreateImageForDeployment;
use App\Jobs\EnsureAppIsPublic;
use App\Jobs\UpdateCloudRunService;
use App\Jobs\WaitForCloudRunServiceToDeploy;
use App\Jobs\WaitForImageToBeBuilt;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Environment extends Model
{
    const INITIAL_ENVIRONMENTS = [
        'production',
    ];

    protected $fillable = [
        'name',
        'url',
    ];

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function deployments()
    {
        return $this->hasMany('App\Deployment');
    }

    public function database()
    {
        return $this->belongsTo('App\Database');
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
     * Create an initial deployment on Cloud Run.
     */
    public function createInitialDeployment()
    {
        // TODO: Make more flexible (support manual pushes, etc)
        $deployment = $this->deployments()->create([
            'commit_hash' => $this->project->sourceProvider->client()->latestHashFor($this->project->repository)
        ]);

        CreateImageForDeployment::withChain([
            new WaitForImageToBeBuilt($deployment),
            new CreateCloudRunService($deployment),
            new WaitForCloudRunServiceToDeploy($deployment),
            new EnsureAppIsPublic($deployment),
        ])->dispatch($deployment);
    }

    /**
     * Create a new deployment on Cloud Run.
     */
    public function deploy()
    {
        // TODO: Pass in an Artifact (Zip bucket location, or GitHub event payload);
        $deployment = $this->deployments()->create();

        CreateImageForDeployment::withChain([
            new WaitForImageToBeBuilt($deployment),
            new UpdateCloudRunService($deployment),
            new WaitForCloudRunServiceToDeploy($deployment),
        ])->dispatch($deployment);
    }

    /**
     * Update the URL on the environment.
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->save();
    }

    public function client(): GoogleApi
    {
        return $this->project->googleProject->client();
    }
}

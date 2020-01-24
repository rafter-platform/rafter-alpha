<?php

namespace App;

use App\Jobs\CreateCloudRunService;
use App\Jobs\CreateImageForDeployment;
use App\Jobs\EnsureAppIsPublic;
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
    ];

    public function project()
    {
        return $this->belongsTo('App\Project');
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
        // TODO: Pass in an Artifact (Zip bucket location, or GitHub event payload);
        $deployment = $this->deployments()->create();

        CreateImageForDeployment::withChain([
            new WaitForImageToBeBuilt($deployment),
            new CreateCloudRunService($deployment),
            new WaitForCloudRunServiceToDeploy($deployment),
            new EnsureAppIsPublic($deployment),
        ])->dispatch($deployment);
    }

    public function client(): GoogleApi
    {
        return $this->project->googleProject->client();
    }
}
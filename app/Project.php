<?php

namespace App;

use App\Jobs\CreateImageForDeployment;
use App\Jobs\WaitForImageToBeBuilt;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'region',
        'google_project_id',
    ];

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function googleProject()
    {
        return $this->belongsTo('App\GoogleProject');
    }

    public function deployments()
    {
        return $this->hasMany('App\Deployment');
    }

    /**
     * Create an initial deployment on Cloud Run.
     *
     * TODO: Extract this out and make it the responsibilty of each environment.
     */
    public function createInitialDeployment()
    {
        // TODO: Pass in an Artifact (Zip bucket location, or GitHub event payload);
        $deployment = $this->deployments()->create();

        CreateImageForDeployment::withChain([
            new WaitForImageToBeBuilt($deployment),
        ])->dispatch($deployment);
    }
}

<?php

namespace App;

use App\Jobs\CreateAppEngineShellApp;
use App\Jobs\DetermineProjectNumber;
use App\Jobs\EnableProjectApis;
use App\Jobs\GrantCloudBuildAccessToSecrets;
use App\Jobs\SyncDatabaseInstances;
use App\Jobs\WaitForProjectApisToBeEnabled;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use App\DatabaseInstance;
use App\Team;

class GoogleProject extends Model
{
    const REQUIRED_APIS = [
        // To enable APIs
        'servicemanagement.googleapis.com',
        'cloudresourcemanager.googleapis.com',
        // IAM
        'iam.googleapis.com',
        // Cloud Run
        'run.googleapis.com',
        // Cloud Build
        'cloudbuild.googleapis.com',
        // MySQL DB
        'sqladmin.googleapis.com',
        // Postgres DB
        'compute.googleapis.com',
        // Cloud Tasks
        'cloudtasks.googleapis.com',
        'appengine.googleapis.com',
        // Secret Manager
        'secretmanager.googleapis.com',
    ];

    const REGIONS = [
        'us-central1' => 'Iowa',
        'us-east1' => 'South Carolina',
        'europe-west1' => 'Belgium',
        'asia-northeast1' => 'Tokyo',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVATING = 'activating';
    const STATUS_READY = 'ready';
    const STATUS_FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'service_account_json' => 'array',
    ];

    protected $hidden = [
        'service_account_json',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function databaseInstances()
    {
        return $this->hasMany(DatabaseInstance::class);
    }

    /**
     * Set up the Google Project with basic requirements
     * for deploying with Rafter.
     */
    public function provision()
    {
        EnableProjectApis::withChain([
            new WaitForProjectApisToBeEnabled($this),
            new DetermineProjectNumber($this),
            new CreateAppEngineShellApp($this),
            new GrantCloudBuildAccessToSecrets($this),
            new SyncDatabaseInstances($this),
        ])->dispatch($this);
    }

    /**
     * Find Project Number for the given project, and save it to the database.
     */
    public function determineProjectNumber()
    {
        $project = $this->client()->getProject();
        $this->update(['project_number' => $project['projectNumber']]);
    }

    /**
     * Set status to activating
     *
     * @return void
     */
    public function setActivating()
    {
        $this->update(['status' => static::STATUS_ACTIVATING]);
    }

    /**
     * Set status to ready
     *
     * @return void
     */
    public function setReady()
    {
        $this->update(['status' => static::STATUS_READY]);
    }

    /**
     * Set status to failed
     *
     * @return void
     */
    public function setFailed()
    {
        $this->update(['status' => static::STATUS_FAILED]);
    }

    public function client(): GoogleApi
    {
        return new GoogleApi($this);
    }
}

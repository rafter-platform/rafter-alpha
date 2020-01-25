<?php

namespace App;

use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;

class GoogleProject extends Model
{
    const REQUIRED_APIS = [
        'run.googleapis.com',
        'cloudbuild.googleapis.com',
        // for DB support
        'sqladmin.googleapis.com',
        // for Postgres DB support
        'compute.googleapis.com',
    ];

    const REGIONS = [
        'us-central1' => 'Iowa',
        'us-east1' => 'South Carolina',
        'europe-west1' => 'Belgium',
        'asia-northeast1' => 'Tokyo',
    ];

    protected $fillable = [
        'name',
        'project_id',
        'project_number',
        'service_account_json',
    ];

    protected $casts = [
        'service_account_json' => 'array',
    ];

    protected $hidden = [
        'service_account_json',
    ];

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    /**
     * Set up the Google Project with basic requirements
     * for deploying with Rafter.
     *
     * TODO: Delegate to chained, queued jobs.
     */
    public function provision()
    {
        $this->determineProjectNumber();
        $this->enableApis();
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
     * Enable all required APIs for use by Rafter.
     */
    public function enableApis()
    {
        $this->client()->enableApis(static::REQUIRED_APIS);
    }

    public function client(): GoogleApi
    {
        return new GoogleApi($this);
    }
}

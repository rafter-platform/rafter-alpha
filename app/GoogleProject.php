<?php

namespace App;

use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;

class GoogleProject extends Model
{
    const REQUIRED_APIS = [
        'run.googleapis.com',
        'cloudbuild.googleapis.com',
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

    public function team()
    {
        return $this->belongsTo('App\Team');
    }

    public function provision()
    {
        $this->getProjectNumber();
        $this->enableApis();
    }

    public function getProjectNumber()
    {
        $project = $this->client()->getProject();
        $this->update(['project_number' => $project['projectNumber']]);
    }

    public function enableApis()
    {
        $this->client()->enableApis(static::REQUIRED_APIS);
    }

    public function client()
    {
        return new GoogleApi($this);
    }
}

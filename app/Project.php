<?php

namespace App;

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

    /**
     * Create an initial deployment on Cloud Run.
     *
     * TODO: Extract this out and make it the responsibilty of each environment.
     */
    public function createInitialDeployment()
    {
        # code...
    }
}

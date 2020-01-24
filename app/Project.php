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

    public function environments()
    {
        return $this->hasMany('App\Environment');
    }

    /**
     * Create the initial environments for the project
     */
    public function createInitialEnvironments()
    {
        collect(Environment::INITIAL_ENVIRONMENTS)
            ->map(function ($name) {
                $this->environments()->create([
                    'name' => $name
                ]);
            })
            ->each
            ->;
    }
}

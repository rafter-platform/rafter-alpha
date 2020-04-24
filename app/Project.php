<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * Possible Project types
     */
    const TYPES = [
        'laravel' => "Laravel",
        'nodejs' => "Node.js",
    ];

    protected $fillable = [
        'name',
        'region',
        'google_project_id',
        'type',
        'repository',
        'source_provider_id',
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

    public function sourceProvider()
    {
        return $this->belongsTo('App\SourceProvider');
    }

    /**
     * Create the initial environments for the project
     */
    public function createInitialEnvironments()
    {
        collect(Environment::INITIAL_ENVIRONMENTS)
            ->each(function ($name) {
                tap($this->environments()->create([
                    'name' => $name
                ]), function ($environment) {
                    $environment->provision();
                });
            });
    }

    /**
     * Whether this project is a Laravel project
     *
     * @return boolean
     */
    public function isLaravel()
    {
        return $this->type === 'laravel';
    }

    public function production()
    {
        return $this->environments()
            ->where('name', 'production')
            ->first();
    }

    public function productionUrl()
    {
        return $this->production()->url ?? '';
    }

    public function typeLabel(): string
    {
        return static::TYPES[$this->type];
    }
}

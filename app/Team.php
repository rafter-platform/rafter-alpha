<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GoogleProject;
use App\DatabaseInstance;
use App\Project;
use App\User;

class Team extends Model
{
    protected $guarded = [];

    protected $casts = [
        'personal_team' => 'boolean',
    ];

    /**
     * Eager load the following relations.
     *
     * @var array
     */
    protected $with = [
        'projects',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function googleProjects()
    {
        return $this->hasMany(GoogleProject::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function databaseInstances()
    {
        return $this->hasManyThrough(DatabaseInstance::class, GoogleProject::class);
    }

    /**
     * Whether the given user belongs to a team.
     *
     * @param User $user
     * @return boolean
     */
    public function hasUser(User $user)
    {
        return $this->owner->is($user) || $this->users->contains($user->id);
    }
}

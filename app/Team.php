<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $guarded = [];

    protected $casts = [
        'personal_team' => 'boolean',
    ];


    public function owner()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function googleProjects()
    {
        return $this->hasMany('App\GoogleProject');
    }

    public function projects()
    {
        return $this->hasMany('App\Project');
    }
}

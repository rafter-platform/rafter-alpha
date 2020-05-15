<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Eager load the following relationships.
     *
     * @var array
     */
    protected $with = [
        'currentTeam',
    ];

    public function ownedTeams()
    {
        return $this->hasMany('App\Team');
    }

    public function teams()
    {
        return $this->belongsToMany('App\Team');
    }

    public function sourceProviders()
    {
        return $this->hasMany('App\SourceProvider');
    }

    public function currentTeam()
    {
        return $this->belongsTo('App\Team', 'current_team_id');
    }

    public function setCurrentTeam(Team $team)
    {
        $this->current_team_id = $team->id;
        $this->save();
    }

    public function avatarUrl(): string
    {
        return sprintf(
            'https://www.gravatar.com/avatar/%s.jpg',
            md5($this->email)
        );
    }
}

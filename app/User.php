<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Team;
use App\SourceProvider;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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
        return $this->hasMany(Team::class);
    }

    public function teams()
    {
        return $this->belongsToMany(Team::class);
    }

    public function sourceProviders()
    {
        return $this->hasMany(SourceProvider::class);
    }

    public function currentTeam()
    {
        return $this->belongsTo(Team::class, 'current_team_id');
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

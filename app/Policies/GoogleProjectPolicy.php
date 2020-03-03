<?php

namespace App\Policies;

use App\GoogleProject;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GoogleProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any google projects.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the google project.
     *
     * @param  \App\User  $user
     * @param  \App\GoogleProject  $googleProject
     * @return mixed
     */
    public function view(User $user, GoogleProject $googleProject)
    {
        return $user->currentTeam->is($googleProject->team);
    }

    /**
     * Determine whether the user can create google projects.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the google project.
     *
     * @param  \App\User  $user
     * @param  \App\GoogleProject  $googleProject
     * @return mixed
     */
    public function update(User $user, GoogleProject $googleProject)
    {
        return $user->currentTeam->is($googleProject->team);
    }

    /**
     * Determine whether the user can delete the google project.
     *
     * @param  \App\User  $user
     * @param  \App\GoogleProject  $googleProject
     * @return mixed
     */
    public function delete(User $user, GoogleProject $googleProject)
    {
        return $user->currentTeam->is($googleProject->team);
    }

    /**
     * Determine whether the user can restore the google project.
     *
     * @param  \App\User  $user
     * @param  \App\GoogleProject  $googleProject
     * @return mixed
     */
    public function restore(User $user, GoogleProject $googleProject)
    {
        return $user->currentTeam->is($googleProject->team);
    }

    /**
     * Determine whether the user can permanently delete the google project.
     *
     * @param  \App\User  $user
     * @param  \App\GoogleProject  $googleProject
     * @return mixed
     */
    public function forceDelete(User $user, GoogleProject $googleProject)
    {
        return $user->currentTeam->is($googleProject->team);
    }
}

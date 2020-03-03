<?php

namespace App\Policies;

use App\DatabaseInstance;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DatabaseInstancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any database instances.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the database instance.
     *
     * @param  \App\User  $user
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return mixed
     */
    public function view(User $user, DatabaseInstance $databaseInstance)
    {
        return $user->currentTeam->googleProjects->contains($databaseInstance->googleProject);
    }

    /**
     * Determine whether the user can create database instances.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the database instance.
     *
     * @param  \App\User  $user
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return mixed
     */
    public function update(User $user, DatabaseInstance $databaseInstance)
    {
        return $user->currentTeam->googleProjects->contains($databaseInstance->googleProject);
    }

    /**
     * Determine whether the user can delete the database instance.
     *
     * @param  \App\User  $user
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return mixed
     */
    public function delete(User $user, DatabaseInstance $databaseInstance)
    {
        return $user->currentTeam->googleProjects->contains($databaseInstance->googleProject);
    }

    /**
     * Determine whether the user can restore the database instance.
     *
     * @param  \App\User  $user
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return mixed
     */
    public function restore(User $user, DatabaseInstance $databaseInstance)
    {
        return $user->currentTeam->googleProjects->contains($databaseInstance->googleProject);
    }

    /**
     * Determine whether the user can permanently delete the database instance.
     *
     * @param  \App\User  $user
     * @param  \App\DatabaseInstance  $databaseInstance
     * @return mixed
     */
    public function forceDelete(User $user, DatabaseInstance $databaseInstance)
    {
        return $user->currentTeam->googleProjects->contains($databaseInstance->googleProject);
    }
}

<?php

namespace App\Policies;

use App\DomainMapping;
use App\Environment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DomainMappingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user, Environment $environment)
    {
        return $user->currentTeam->is($environment->project->team);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\User  $user
     * @param  \App\DomainMapping  $domainMapping
     * @return mixed
     */
    public function view(User $user, DomainMapping $domainMapping)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\User  $user
     * @param  \App\DomainMapping  $domainMapping
     * @return mixed
     */
    public function update(User $user, DomainMapping $domainMapping)
    {
        return $user->currentTeam->is($domainMapping->environment->project->team);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\DomainMapping  $domainMapping
     * @return mixed
     */
    public function delete(User $user, DomainMapping $domainMapping)
    {
        return $user->currentTeam->is($domainMapping->environment->project->team);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\User  $user
     * @param  \App\DomainMapping  $domainMapping
     * @return mixed
     */
    public function restore(User $user, DomainMapping $domainMapping)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\User  $user
     * @param  \App\DomainMapping  $domainMapping
     * @return mixed
     */
    public function forceDelete(User $user, DomainMapping $domainMapping)
    {
        //
    }
}

<?php

namespace App\Policies;

use App\Deployment;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeploymentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any deployments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the deployment.
     *
     * @param  \App\User  $user
     * @param  \App\Deployment  $deployment
     * @return mixed
     */
    public function view(User $user, Deployment $deployment)
    {
        return $user->currentTeam->is($deployment->environment->project->team);
    }

    /**
     * Determine whether the user can create deployments.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the deployment.
     *
     * @param  \App\User  $user
     * @param  \App\Deployment  $deployment
     * @return mixed
     */
    public function update(User $user, Deployment $deployment)
    {
        return $user->currentTeam->is($deployment->environment->project->team);
    }

    /**
     * Determine whether the user can update the deployment.
     *
     * @param  \App\User  $user
     * @param  \App\Deployment  $deployment
     * @return mixed
     */
    public function redeploy(User $user, Deployment $deployment)
    {
        return $user->currentTeam->is($deployment->environment->project->team);
    }

    /**
     * Determine whether the user can delete the deployment.
     *
     * @param  \App\User  $user
     * @param  \App\Deployment  $deployment
     * @return mixed
     */
    public function delete(User $user, Deployment $deployment)
    {
        return $user->currentTeam->is($deployment->environment->project->team);
    }

    /**
     * Determine whether the user can restore the deployment.
     *
     * @param  \App\User  $user
     * @param  \App\Deployment  $deployment
     * @return mixed
     */
    public function restore(User $user, Deployment $deployment)
    {
        return $user->currentTeam->is($deployment->environment->project->team);
    }

    /**
     * Determine whether the user can permanently delete the deployment.
     *
     * @param  \App\User  $user
     * @param  \App\Deployment  $deployment
     * @return mixed
     */
    public function forceDelete(User $user, Deployment $deployment)
    {
        return $user->currentTeam->is($deployment->environment->project->team);
    }
}

<?php

namespace App\Policies;

use App\Command;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommandPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any commands.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the command.
     *
     * @param  \App\User  $user
     * @param  \App\Command  $command
     * @return mixed
     */
    public function view(User $user, Command $command)
    {
        return $user->currentTeam->is($command->environment->project->team);
    }

    /**
     * Determine whether the user can create commands.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the command.
     *
     * @param  \App\User  $user
     * @param  \App\Command  $command
     * @return mixed
     */
    public function update(User $user, Command $command)
    {
        //
    }

    /**
     * Determine whether the user can delete the command.
     *
     * @param  \App\User  $user
     * @param  \App\Command  $command
     * @return mixed
     */
    public function delete(User $user, Command $command)
    {
        //
    }

    /**
     * Determine whether the user can restore the command.
     *
     * @param  \App\User  $user
     * @param  \App\Command  $command
     * @return mixed
     */
    public function restore(User $user, Command $command)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the command.
     *
     * @param  \App\User  $user
     * @param  \App\Command  $command
     * @return mixed
     */
    public function forceDelete(User $user, Command $command)
    {
        //
    }
}

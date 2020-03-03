<?php

namespace App\Policies;

use App\SourceProvider;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SourceProviderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any source providers.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can view the source provider.
     *
     * @param  \App\User  $user
     * @param  \App\SourceProvider  $sourceProvider
     * @return mixed
     */
    public function view(User $user, SourceProvider $sourceProvider)
    {
        return $user->is($sourceProvider->user);
    }

    /**
     * Determine whether the user can create source providers.
     *
     * @param  \App\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the source provider.
     *
     * @param  \App\User  $user
     * @param  \App\SourceProvider  $sourceProvider
     * @return mixed
     */
    public function update(User $user, SourceProvider $sourceProvider)
    {
        return $user->is($sourceProvider->user);
    }

    /**
     * Determine whether the user can delete the source provider.
     *
     * @param  \App\User  $user
     * @param  \App\SourceProvider  $sourceProvider
     * @return mixed
     */
    public function delete(User $user, SourceProvider $sourceProvider)
    {
        return $user->is($sourceProvider->user);
    }

    /**
     * Determine whether the user can restore the source provider.
     *
     * @param  \App\User  $user
     * @param  \App\SourceProvider  $sourceProvider
     * @return mixed
     */
    public function restore(User $user, SourceProvider $sourceProvider)
    {
        return $user->is($sourceProvider->user);
    }

    /**
     * Determine whether the user can permanently delete the source provider.
     *
     * @param  \App\User  $user
     * @param  \App\SourceProvider  $sourceProvider
     * @return mixed
     */
    public function forceDelete(User $user, SourceProvider $sourceProvider)
    {
        return $user->is($sourceProvider->user);
    }
}

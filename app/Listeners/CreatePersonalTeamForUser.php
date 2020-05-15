<?php

namespace App\Listeners;

use App\Events\UserRegistered;

class CreatePersonalTeamForUser
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        $user = $event->user;

        $team = $user->ownedTeams()->create([
            'name' => 'Personal',
            'personal_team' => true,
        ]);

        $user->setCurrentTeam($team);
    }
}

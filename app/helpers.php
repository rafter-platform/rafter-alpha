<?php

/**
 * Get the user's current team
 *
 * @return \App\Team|null
 */
function currentTeam()
{
    return optional(auth()->user()->currentTeam);
}

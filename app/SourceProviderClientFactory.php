<?php

namespace App;

use App\Services\GitHub;
use App\SourceProvider;
use InvalidArgumentException;

class SourceProviderClientFactory
{
    /**
     * Create a server provider client instance for the given provider.
     *
     * @param  \App\SourceProvider  $provider
     * @return \App\Contracts\SourceProviderClient
     */
    public static function make(SourceProvider $provider)
    {
        switch ($provider->type) {
            case 'GitHub':
                return new GitHub($provider);
            default:
                throw new InvalidArgumentException("Invalid provider type.");
        }
    }
}

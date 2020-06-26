<?php

namespace App;

use App\Services\GitHub;
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
            case SourceProvider::TYPE_GITHUB:
                return app(GitHub::class, ['source' => $provider]);
            default:
                throw new InvalidArgumentException("Invalid provider type.");
        }
    }
}

<?php

namespace App;

use App\Casts\Options;
use Error;
use Illuminate\Database\Eloquent\Model;

class SourceProvider extends Model
{
    protected $casts = [
        'meta' => Options::class,
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * Get the token for this source provider.
     *
     * @return string
     */
    public function token(): string
    {
        return $this->meta['token'];
    }

    /**
     * Get a source control provider client for the provider.
     *
     * @return \App\Contracts\SourceProviderClient
     */
    public function client()
    {
        return SourceProviderClientFactory::make($this);
    }

    public function refreshGitHubInstallation()
    {
        if ($this->type != 'GitHub') {
            throw new Error('Only GitHub source providers can be refreshed.');
        }

        $this->client()->refreshInstallation();
    }
}

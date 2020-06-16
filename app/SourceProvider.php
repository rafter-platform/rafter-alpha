<?php

namespace App;

use App\Casts\Options;
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
}

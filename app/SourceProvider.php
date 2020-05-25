<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SourceProvider extends Model
{
    protected $casts = [
        'meta' => 'array',
    ];

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
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

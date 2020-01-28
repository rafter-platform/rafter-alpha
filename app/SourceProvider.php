<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SourceProvider extends Model
{
    protected $casts = [
        'meta' => 'array',
    ];

    protected $fillable = [
        'name',
        'type',
        'meta',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
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

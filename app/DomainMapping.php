<?php

namespace App;

use App\GoogleCloud\DomainMappingConfig;
use Illuminate\Database\Eloquent\Model;

class DomainMapping extends Model
{
    const STATUS_INACTIVE = 'inactive';
    const STATUS_UNVERIFIED = 'unverified';
    const STATUS_PENDING_VERIFICATION = 'pending_verification';
    const STATUS_PENDING_CERTIFICATE = 'pending_certificate';
    const STATUS_ACTIVE = 'pending_certificate';

    protected $fillable = [
        'domain',
        'status',
    ];

    public function environment()
    {
        return $this->belongsTo('App\Environment');
    }

    /**
     * Provision a domain mapping to Google Cloud.
     *
     * @return void
     */
    public function provision()
    {
        $response = $this->environment->client()->addCloudRunDomainMapping(new DomainMappingConfig($this));

        dump($response);
    }
}

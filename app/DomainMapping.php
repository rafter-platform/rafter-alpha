<?php

namespace App;

use App\GoogleCloud\DomainMappingConfig;
use App\GoogleCloud\DomainMappingResponse;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;

class DomainMapping extends Model
{
    /**
     * Mapping has been created by the user, but has not yet been checked by Rafter.
     */
    const STATUS_INACTIVE = 'inactive';

    /**
     * Something unexpected while adding the domain, e.g. the domain is malformed.
     */
    const STATUS_ERROR = 'error';

    /**
     * Mapping has been added by Rafter, but the domain is unverified. User needs to
     * take action to verify it, after which Rafter will change the status.
     */
    const STATUS_UNVERIFIED = 'unverified';

    /**
     * Domain is verified by the user, and Mapping is added by Rafter. Cloud Run
     * is waiting for DNS records to be added by user and to propogate. Rafter should keep
     * checking as well as prompting the user to add the records.
     */
    const STATUS_PENDING_DNS = 'pending_dns';

    /**
     * Cloud Run recognizes that the records have been added, and it is attempting to
     * provision the TLS certificate for the domain.
     */
    const STATUS_PENDING_CERTIFICATE = 'pending_certificate';

    /**
     * Everything checks out and is ready to go.
     */
    const STATUS_ACTIVE = 'active';

    protected $fillable = [
        'domain',
        'status',
    ];

    public function environment()
    {
        return $this->belongsTo('App\Environment');
    }

    public function getMapping(): DomainMappingResponse
    {
        return $this->client()->getCloudRunDomainMapping($this);
    }

    public function markUnverified()
    {
        $this->update(['status' => static::STATUS_UNVERIFIED]);
    }

    public function markPendingDns()
    {
        $this->update(['status' => static::STATUS_PENDING_DNS]);
    }

    public function markPendingCertificate()
    {
        $this->update(['status' => static::STATUS_PENDING_CERTIFICATE]);
    }

    public function markActive()
    {
        $this->update(['status' => static::STATUS_ACTIVE]);
    }

    /**
     * Provision a domain mapping to Google Cloud.
     *
     * @return void
     */
    public function provision()
    {
        try {
            $response = $this->environment->client()->addCloudRunDomainMapping(new DomainMappingConfig($this));

            // TODO: Set status and message based on response.
            dump($response);
        } catch (RequestException $e) {
            $this->update(['message' => $e->getMessage()]);
        }
    }

    public function checkStatus()
    {
        $mapping = $this->getMapping();

        if ($mapping->isUnverified()) {
            $this->markUnverified();
            return;
        }

        if ($mapping->isPendingDns()) {
            $this->markPendingDns();
            return;
        }

        if ($mapping->isPendingCertificate()) {
            $this->markPendingCertificate();
            return;
        }

        if ($mapping->isActive()) {
            $this->markActive();
            return;
        }
    }

    public function client(): GoogleApi
    {
        return $this->environment->client();
    }
}

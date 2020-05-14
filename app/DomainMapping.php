<?php

namespace App;

use App\GoogleCloud\DomainMappingConfig;
use App\GoogleCloud\DomainMappingResponse;
use App\Jobs\CheckDomainMappingStatus;
use App\Services\GoogleApi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\RequestException;
use Throwable;

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
     * Everything checks out and is ready to go.
     */
    const STATUS_ACTIVE = 'active';

    protected $guarded = [];

    protected static function booted()
    {
        static::deleting(function ($mapping) {
            if ($mapping->isErrored()) return;

            $mapping->client()->deleteCloudRunDomainMapping($mapping);
        });
    }

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
        $message = sprintf(
            "In order to add this domain, Cloud Run needs to verify that this service account is a verified owner of %s. "
                . " To proceed, please add <code>%s</code> as a <a href=\"%s\" target=\"_blank\">verified owner on Webmaster Central</a>."
                . " When you've added the service account, click the button below.",
            $this->domain,
            $this->serviceAccountEmail(),
            $this->webmasterCentralUrl()
        );

        $this->update([
            'status' => static::STATUS_UNVERIFIED,
            'message' => $message,
        ]);
    }

    public function markPendingDns(array $records)
    {
        $message = view('environments._domain-mapping-dns-records-pending', [
            'records' => $records,
        ]);

        $this->update([
            'status' => static::STATUS_PENDING_DNS,
            'message' => $message,
        ]);
    }

    public function markActive()
    {
        $this->update([
            'status' => static::STATUS_ACTIVE,
            'message' => '',
        ]);
    }

    public function markInactive($message = '')
    {
        $this->update([
            'status' => static::STATUS_INACTIVE,
            'message' => $message,
        ]);
    }

    public function markError(Throwable $error)
    {
        $message = $error->getMessage();

        if ($error instanceof RequestException) {
            if ($error->getCode() == 404) {
                $message = "Your domain was deleted from Cloud Run's dashboard. Please delete it locally.";
            } else {
                $message = "Your domain did not meet Cloud Run's guidelines. Please delete and try a different domain.";
            }
        }

        $this->update([
            'status' => static::STATUS_ERROR,
            'message' => $message,
        ]);
    }

    public function isErrored()
    {
        return $this->status == static::STATUS_ERROR;
    }

    public function isUnverified()
    {
        return $this->status == static::STATUS_UNVERIFIED;
    }

    public function isPendingDns()
    {
        return $this->status == static::STATUS_PENDING_DNS;
    }

    public function isInactive()
    {
        return $this->status == static::STATUS_INACTIVE;
    }

    /**
     * Provision a domain mapping to Google Cloud.
     *
     * @return void
     */
    public function provision()
    {
        try {
            $this->environment->client()->addCloudRunDomainMapping(new DomainMappingConfig($this));

            $this->markInactive('Rafter is adding your domain to Cloud Run...');

            CheckDomainMappingStatus::dispatch($this->id)->delay(3);
        } catch (RequestException $e) {
            $this->markError($e);
        }
    }

    /**
     * Resubmit a domain mapping to Cloud Run after the user has indicated that they have verified the domain.
     * The only way to do this is to delete and re-add the domain mapping via the API.
     *
     * @return void
     */
    public function resubmitAfterVerification()
    {
        try {
            // Set the status to inactive to show immediate feedback to the user
            $this->markInactive('Rafter is resubmitting your domain to Cloud Run after verification.');

            $this->environment->client()->deleteCloudRunDomainMapping($this);

            sleep(1);

            $this->environment->client()->addCloudRunDomainMapping(new DomainMappingConfig($this));

            CheckDomainMappingStatus::dispatch($this->id)->delay(1);
        } catch (RequestException $e) {
            $this->markError($e);
        }
    }

    /**
     * Checks the status of the domain mapping on Cloud Run, and updates the local status accordingly.
     * Additionally, if the domain is pending DNS or Certificate updates, it will re-check after a given time period.
     *
     * @return void
     */
    public function checkStatus($manuallyTriggered = false)
    {
        try {
            $mapping = $this->getMapping();
        } catch (Throwable $e) {
            $this->markError($e);
            return;
        }

        if ($mapping->isActive()) {
            $this->markActive();
            return;
        }

        if ($mapping->isUnverified()) {
            $this->markUnverified();
            return;
        }

        if ($mapping->isPendingDns()) {
            $this->markPendingDns($mapping->dnsRecords());
        }

        if (!$manuallyTriggered) {
            CheckDomainMappingStatus::dispatch($this->id)->delay(15);
        }
    }

    /**
     * Whether the status can be manually checked by the user. Only permitted for pending DNS things,
     * or if the initial check is... slow.
     *
     * @return boolean
     */
    public function canManuallyCheckStatus()
    {
        return $this->isPendingDns() || $this->isInactive();
    }

    public function client(): GoogleApi
    {
        return $this->environment->client();
    }

    protected function serviceAccountEmail(): string
    {
        return $this->environment->serviceAccountEmail();
    }

    protected function webmasterCentralUrl(): string
    {
        return 'https://www.google.com/webmasters/verification/verification?domain=' . $this->domain;
    }

    public function scopeActive(Builder $query)
    {
        $query->where('status', '=', static::STATUS_ACTIVE);
    }
}

<?php

namespace App;

use App\GoogleCloud\DomainMappingConfig;
use App\GoogleCloud\DomainMappingResponse;
use App\Jobs\CheckDomainMappingStatus;
use App\Services\GoogleApi;
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
     * Cloud Run recognizes that the records have been added, and it is attempting to
     * provision the TLS certificate for the domain.
     */
    const STATUS_PENDING_CERTIFICATE = 'pending_certificate';

    /**
     * Everything checks out and is ready to go.
     */
    const STATUS_ACTIVE = 'active';

    protected $guarded = [];

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
                . " To proceed, please add <code>%s</code> as a <a href=\"%s\" target=\"_blank\">verified owner on Webmaster Central</a>.",
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
        $message = 'Add the following DNS records to your registrar to point the domain to Cloud Run:<br />';

        // TODO: Extract this into a view partial
        $message .= '<table><thead><tr><th>Type</th><th>Name</th><th>Content</th></tr></thead><tbody>';

        foreach ($records as $record) {
            $message .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td></tr>',
                $record['type'],
                $record['name'],
                $record['rrdata'],
            );
        }

        $message .= '</tbody></table>';

        $this->update([
            'status' => static::STATUS_PENDING_DNS,
            'message' => $message,
        ]);
    }

    public function markPendingCertificate()
    {
        $this->update([
            'status' => static::STATUS_PENDING_CERTIFICATE,
            'message' => "A certificate is being issued for this domain. This may take up to 15 minutes."
        ]);
    }

    public function markActive()
    {
        $this->update([
            'status' => static::STATUS_ACTIVE,
            'message' => '',
        ]);
    }

    public function markError(Throwable $error)
    {
        $message = $error->getMessage();

        if ($error instanceof RequestException) {
            $message = "Your domain did not meet Cloud Run's guidelines. Please delete and try a different domain.";
        }

        $this->update([
            'status' => static::STATUS_ERROR,
            'message' => $message,
        ]);
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

            CheckDomainMappingStatus::dispatch($this)->delay(3);
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
    public function checkStatus()
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

        if ($mapping->isPendingCertificate()) {
            $this->markPendingCertificate();
        }

        CheckDomainMappingStatus::dispatch($this)->delay(15);

        return;
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
}

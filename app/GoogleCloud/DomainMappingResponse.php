<?php

namespace App\GoogleCloud;

use Illuminate\Support\Str;

class DomainMappingResponse
{
    protected $response;

    /**
     * At the time of writing, the only indicator to mark the difference between "DNS Pending"
     * and "Certificate Pending" is this string of text in the message of the `CertificateProvisioned`
     * step. This is a hack, and it will likely break! Let's hope the Cloud Run API is updated to include
     * a concrete step.
     */
    const DNS_MESSAGE_INDICATOR = 'DNS is not properly configured';

    public function __construct($response)
    {
        $this->response = $response;
    }

    protected function getCondition(string $type)
    {
        return collect($this->response['status']['conditions'] ?? [])
            ->firstWhere('type', $type);
    }

    protected function getConditionStatus(string $type)
    {
        return $this->getCondition($type)['status'] ?? '';
    }

    protected function getConditionMessage(string $type)
    {
        return $this->getCondition($type)['message'] ?? '';
    }

    public function isUnverified(): bool
    {
        return $this->getConditionStatus('DomainRoutable') == 'False';
    }

    public function isPendingDns(): bool
    {
        return $this->getConditionStatus('DomainRoutable') == 'True'
            && $this->getConditionStatus('CertificateProvisioned') == 'Unknown'
            && Str::of($this->getConditionMessage('CertificateProvisioned'))->contains(static::DNS_MESSAGE_INDICATOR);
    }

    public function isPendingCertificate(): bool
    {
        return $this->getConditionStatus('DomainRoutable') == 'True'
            && $this->getConditionStatus('CertificateProvisioned') == 'Unknown'
            && !Str::of($this->getConditionMessage('CertificateProvisioned'))->contains(static::DNS_MESSAGE_INDICATOR);
    }

    public function isActive(): bool
    {
        return $this->getConditionStatus('Ready') == 'True'
            && $this->getConditionStatus('CertificateProvisioned') == 'True'
            && $this->getConditionStatus('DomainRoutable') == 'True';
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function dnsRecords(): array
    {
        return $this->response['status']['resourceRecords'] ?? [];
    }
}

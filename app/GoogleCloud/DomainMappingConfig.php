<?php

namespace App\GoogleCloud;

use App\DomainMapping;

class DomainMappingConfig
{
    protected $domainMapping;

    public function __construct(DomainMapping $domainMapping) {
        $this->domainMapping = $domainMapping;
    }

    public function region()
    {
        return $this->domainMapping->environment->region();
    }

    protected function metadata()
    {
        return [
            'name' => $this->domainMapping->domain,
        ];
    }

    protected function spec()
    {
        return [
            'routeName' => $this->domainMapping->environment->web_service_name,
        ];
    }

    public function config(): array
    {
        return [
            'apiVersion' => 'domains.cloudrun.com/v1',
            'kind' => 'DomainMapping',
            'metadata' => $this->metadata(),
            'spec' => $this->spec(),
        ];
    }
}

<?php

namespace App\Http\Livewire;

use App\DomainMapping;
use App\Environment;
use App\Jobs\ReverifyDomainMapping;
use App\Rules\ValidDomain;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EnvironmentDomains extends Component
{
    use AuthorizesRequests;

    public $environment;

    public $domain;

    public function mount(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function updated($field)
    {
        $this->validateOnly($field, [
            'domain' => [
                'required',
                'min:6',
                new ValidDomain
            ],
        ]);
    }

    public function addDomain()
    {
        $data = $this->validate([
            'domain' => [
                'required',
                'min:6',
                new ValidDomain
            ],
        ]);

        $this->environment->addDomainMapping($data);
        $this->domain = '';
    }

    public function deleteDomain($id)
    {
        $mapping = DomainMapping::find($id);

        $this->authorize('delete', [$mapping]);

        $mapping->delete();
    }

    public function verifyDomain($id)
    {
        $mapping = DomainMapping::find($id);

        $this->authorize('update', [$mapping]);

        ReverifyDomainMapping::dispatch($mapping->id);
    }

    public function checkDomainStatus($id)
    {
        $mapping = DomainMapping::find($id);

        $this->authorize('update', [$mapping]);

        $mapping->checkStatus(true);
    }

    public function render()
    {
        return view('livewire.environment-domains', [
            'mappings' => $this->environment->domainMappings,
        ]);
    }
}

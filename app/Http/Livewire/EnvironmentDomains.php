<?php

namespace App\Http\Livewire;

use App\DomainMapping;
use App\Environment;
use App\Rules\ValidDomain;
use Livewire\Component;

class EnvironmentDomains extends Component
{
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
        $mapping->delete();
    }

    public function render()
    {
        return view('livewire.environment-domains', [
            'mappings' => $this->environment->domainMappings,
        ]);
    }
}

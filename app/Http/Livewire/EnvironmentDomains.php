<?php

namespace App\Http\Livewire;

use App\Environment;
use Livewire\Component;
use Illuminate\Support\Str;

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
                /**
                 * TODO: Extract this rule; also, this is pretty sucky. Need to make it more
                 * bulletproof to allow a user to pick a [sub]domain without allowing them to
                 * specify a path. But also validate that domain.
                 */
                function ($attribute, $value, $fail) {
                    if ($value == '') return;

                    $value = Str::start($value, 'https://');

                    $url = parse_url($value);

                    if ($url === false) {
                        $fail('Please enter a valid domain');
                        return;
                    }

                    if (isset($url['path']) && $url['path'] != '' && $url['path'] != '/') {
                        $fail('Only domains and subdomains may be used; it is not possible to map services to paths.');
                    }
                },
            ],
        ]);
    }

    public function addDomain()
    {
        $data = $this->validate([
            'domain' => 'required|min:6',
        ]);

        $this->environment->addDomainMapping($data);
        $this->domain = '';
    }

    public function render()
    {
        return view('livewire.environment-domains', [
            'mappings' => $this->environment->domainMappings,
        ]);
    }
}

<?php

namespace App\Http\Livewire;

use App\Environment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EnvironmentServiceSettings extends Component
{
    use AuthorizesRequests;

    public $environment;
    public $type;

    public $memory;
    public $cpu;
    public $requestTimeout;
    public $maxRequestsPerContainer;
    public $maxInstances;

    public $memoryOptions = [
        '128Mi' => '128 MB',
        '256Mi' => '256 MB',
        '512Mi' => '512 MB',
        '1Gi' => '1 GB',
        '2Gi' => '2 GB',
    ];

    public function mount(Environment $environment, $type)
    {
        $this->environment = $environment;
        $this->type = $type;

        $this->memory = $environment->getOption($type . '_memory');
        $this->cpu = $environment->getOption($type . '_cpu');
        $this->requestTimeout = $environment->getOption($type . '_request_timeout');
        $this->maxRequestsPerContainer = $environment->getOption($type . '_max_requests_per_container');
        $this->maxInstances = $environment->getOption($type . '_max_instances');
    }

    public function render()
    {
        return view('livewire.environment-service-settings');
    }

    protected function rules()
    {
        return [
            'memory' => ['required', Rule::in(array_keys($this->memoryOptions))],
            'cpu' => ['required', Rule::in([1, 2])],
            'requestTimeout' => ['required', 'integer', 'between:1,900'],
            'maxRequestsPerContainer' => ['required', 'integer', 'between:1,80'],
            'maxInstances' => ['required', 'integer', 'between:1,1000'],
        ];
    }

    public function updated()
    {
        $this->validate($this->rules());
    }

    public function handle()
    {
        $this->authorize('update', $this->environment);

        $data = $this->validate($this->rules());

        $this->environment->setOption($this->type . '_memory', $data['memory']);
        $this->environment->setOption($this->type . '_cpu', $data['cpu']);
        $this->environment->setOption($this->type . '_request_timeout', $data['requestTimeout']);
        $this->environment->setOption($this->type . '_max_requests_per_container', $data['maxRequestsPerContainer']);
        $this->environment->setOption($this->type . '_max_instances', $data['maxInstances']);
    }
}

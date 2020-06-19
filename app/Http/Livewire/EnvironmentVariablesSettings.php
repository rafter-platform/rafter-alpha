<?php

namespace App\Http\Livewire;

use App\Environment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EnvironmentVariablesSettings extends Component
{
    use AuthorizesRequests;

    public $environment;
    public $variables;

    public function mount(Environment $environment)
    {
        $this->environment = $environment;
        $this->variables = $environment->environmental_variables;
    }

    public function render()
    {
        return view('livewire.environment-variables-settings');
    }

    public function handle()
    {
        $this->authorize('update', $this->environment);

        $this->validate([
            'variables' => ['string'],
        ]);

        $this->environment->update(['environmental_variables' => $this->variables]);
        logger($this->variables);

        $this->dispatchBrowserEvent('notify', 'Settings saved!');
    }
}

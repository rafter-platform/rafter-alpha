<?php

namespace App\Http\Livewire;

use App\Environment;
use App\Rules\ValidBranch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EnvironmentSourceControlSettings extends Component
{
    use AuthorizesRequests;

    public $environment;
    public $waitForChecks;
    public $branch;

    public function mount(Environment $environment)
    {
        $this->environment = $environment;
        $this->waitForChecks = $environment->getOption('wait_for_checks');
        $this->branch = $environment->branch;
    }

    public function render()
    {
        return view('livewire.environment-source-control-settings');
    }

    public function updated()
    {
        return $this->validate($this->rules());
    }

    protected function rules()
    {
        return [
            'branch' => [
                'required',
                new ValidBranch(
                    $this->environment->sourceProvider(),
                    $this->environment->repository()
                )
            ],
            'waitForChecks' => [
                'required',
                'boolean'
            ],
        ];
    }

    public function handle()
    {
        $this->authorize('update', $this->environment);

        $this->validate($this->rules());

        $this->environment->branch = $this->branch;
        $this->environment->setOption('wait_for_checks', $this->waitForChecks);
        $this->environment->save();

        $this->dispatchBrowserEvent('notify', 'Settings saved!');
    }
}

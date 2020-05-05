<?php

namespace App\Http\Livewire;

use App\Environment;
use Livewire\Component;
use Livewire\WithPagination;

class DeploymentsList extends Component
{
    use WithPagination;

    public $environment;

    public function mount(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function render()
    {
        return view('livewire.deployments-list', [
            'deployments' => $this->environment->deployments()->paginate(10),
        ]);
    }

    public function deployNow()
    {
        $deployment = $this->environment->deploy(request()->user()->id);

        return redirect()->to($deployment->getRoute());
    }
}

<?php

namespace App\Http\Livewire;

use Livewire\Component;

class DeploymentStatus extends Component
{
    public $deployment;

    public function mount($deployment)
    {
        $this->deployment = $deployment;
    }

    public function render()
    {
        return view('livewire.deployment-status', [
            'deployment' => $this->deployment,
        ]);
    }
}

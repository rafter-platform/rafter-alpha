<?php

namespace App\Http\Livewire;

use App\Environment;
use Livewire\Component;

class EnvironmentMetrics extends Component
{
    public function mount(Environment $environment)
    {
        # code...
    }

    public function render()
    {
        return view('livewire.environment-metrics');
    }
}

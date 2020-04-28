<?php

namespace App\Http\Livewire;

use App\Environment;
use Livewire\Component;

class CommandsList extends Component
{
    public $environment;

    public function mount(Environment $environment)
    {
        $this->environment = $environment;
    }

    public function render()
    {
        return view('livewire.commands-list');
    }
}

<?php

namespace App\Http\Livewire;

use App\Command;
use Livewire\Component;

class CommandOutput extends Component
{
    public $command;

    public function mount(Command $command)
    {
        $this->command = $command;
    }

    public function render()
    {
        return view('livewire.command-output');
    }

    public function getLabelProperty(): string
    {
        if ($this->command->isRunning()) {
            return 'Started running';
        } elseif ($this->command->isFinished()) {
            return 'Ran';
        } elseif ($this->command->isFailed()) {
            return 'Failed';
        }

        return 'Created';
    }
}

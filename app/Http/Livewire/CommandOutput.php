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
        return view('livewire.command-output', [
            'output' => $this->getOutput(),
        ]);
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

    public function getOutput(): string
    {
        return "$ php artisan {$this->command->command}\r\n\r\n{$this->command->output}";
    }
}

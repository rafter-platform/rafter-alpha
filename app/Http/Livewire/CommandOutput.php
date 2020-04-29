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

    public function getBackLinkProperty(): string
    {
        return route('projects.environments.commands.index', [
            $this->command->environment->project,
            $this->command->environment
        ]);
    }

    public function getOutput(): string
    {
        return "$ php artisan {$this->command->command}\r\n\r\n{$this->command->output}";
    }
}

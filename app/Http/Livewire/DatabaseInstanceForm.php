<?php

namespace App\Http\Livewire;

use App\DatabaseInstance;
use Livewire\Component;

class DatabaseInstanceForm extends Component
{
    public $type = 'mysql';
    public $version;
    public $tier;
    public $size;
    public $name;

    public function mount()
    {
        $instance = new DatabaseInstance;

        $this->version = $instance->getOption('version');
        $this->tier = $instance->getOption('tier');
        $this->size = $instance->getOption('size');
    }

    public function render()
    {
        return view('livewire.database-instance-form', [
            'versions' => DatabaseInstance::VERSIONS,
            'tiers' => DatabaseInstance::TIERS['mysql'],
        ]);
    }
}

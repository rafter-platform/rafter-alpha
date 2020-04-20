<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DropdownMenu extends Component
{
    public $label;

    public function __construct($label)
    {
        $this->label = $label;
    }

    public static function iconClass()
    {
        return 'mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 group-focus:text-gray-500';
    }

    public function render()
    {
        return view('components.dropdown-menu');
    }
}

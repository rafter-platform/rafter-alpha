<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DropdownMenuItem extends Component
{
    public $href;
    public $hasIcon;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($href = '', $hasIcon = false)
    {
        $this->href = $href;
        $this->hasIcon = $hasIcon;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.dropdown-menu-item');
    }

    public function classList()
    {
        $classes = 'px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:bg-gray-100 focus:text-gray-900 ';

        $classes .= $this->hasIcon ? 'group flex items-center' : 'block w-full text-left';

        return $classes;
    }
}

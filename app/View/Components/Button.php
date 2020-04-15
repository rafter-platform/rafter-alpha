<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public $color;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($color = 'blue')
    {
        $this->color = $color;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.button');
    }

    /**
     * Get all the classes for the button
     *
     * @return string
     */
    public function classList()
    {
        $color = $this->color;
        return "bg-$color-500 text-gray-100 font-bold py-2 px-4 rounded inline-block hover:bg-$color-700 focus:outline-none focus:shadow-outline";
    }
}

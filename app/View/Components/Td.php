<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Td extends Component
{
    public $last;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($last = false)
    {
        $this->last = $last;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.td');
    }

    public function classList()
    {
        $classes = 'px-6 py-4 whitespace-no-wrap text-sm leading-5 text-gray-900';

        if ($this->last) {
            $classes .= ' text-right font-medium';
        }

        return $classes;
    }
}

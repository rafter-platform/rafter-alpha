<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Th extends Component
{
    protected $last;

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
        return view('components.th');
    }

    public function classList()
    {
        if ($this->last) {
            return 'px-6 py-3 border-b border-gray-200 bg-gray-50';
        }

        return 'px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 uppercase tracking-wider';
    }
}

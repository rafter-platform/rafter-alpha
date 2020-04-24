<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TabItem extends Component
{
    public $href;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($href = '')
    {
        $this->href = $href;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.tab-item');
    }

    public function classList()
    {
        if ($this->isActive()) {
            return "whitespace-no-wrap py-4 px-1 border-b-2 border-indigo-500 font-medium text-sm leading-5 text-indigo-600 hover:text-indigo-800 focus:outline-none focus:text-indigo-800 focus:border-indigo-700";
        }

        return "whitespace-no-wrap py-4 px-1 border-b-2 border-transparent font-medium text-sm leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300";
    }

    public function isActive()
    {
        return request()->url() == $this->href;
    }
}

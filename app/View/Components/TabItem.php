<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class TabItem extends Component
{
    public $href;
    public $exact;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($href = '', $exact = false)
    {
        $this->href = $href;
        $this->exact = $exact;
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
            return "whitespace-no-wrap mr-8 py-4 px-1 border-b-2 border-blue-500 font-medium text-sm leading-5 text-blue-600 hover:text-blue-800 focus:outline-none focus:text-blue-800 focus:border-blue-700";
        }

        return "whitespace-no-wrap mr-8 py-4 px-1 border-b-2 border-transparent font-medium text-sm leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300";
    }

    public function isActive()
    {
        if ($this->exact) {
            return request()->url() == $this->href;
        }

        return Str::startsWith(request()->url(), $this->href);
    }
}

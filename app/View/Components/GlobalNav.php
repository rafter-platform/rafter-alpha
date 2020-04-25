<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\Support\Str;

class GlobalNav extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.global-nav');
    }

    public function items(): array
    {
        return [
            'Dashboard' => route('home'),
            'Databases' => route('database-instances.index'),
        ];
    }

    public function profileItems(): array
    {
        return [
            'Your Profile' => '#',
            'Settings' => '#',
        ];
    }

    public function isActive($url): bool
    {
        return Str::startsWith(request()->url(), $url);
    }
}

<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Status extends Component
{
    public $status;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.status');
    }

    /**
     * Get the correct color for the status.
     *
     * @return string
     */
    public function color()
    {
        switch (strtolower($this->status)) {
            case 'pending':
            case 'in_progress':
            case 'creating':
            case 'started':
            case 'unverified':
            case 'pending_dns':
                $color = 'yellow';
                break;

            case 'ready':
            case 'done':
            case 'finished':
            case 'active':
            case 'successful':
                $color = 'green';
                break;

            case 'failed':
            case 'error':
                $color = 'red';
                break;

            default:
                $color = 'gray';
                break;
        }

        return $color;
    }

    /**
     * Get the classList for this status
     *
     * @return string
     */
    public function classList()
    {
        return "inline-block uppercase tracking-wide text-xs text-{$this->color()}-600 bg-{$this->color()}-200 rounded p-1 px-2";
    }

    public function statusDisplay()
    {
        return str_replace('_', ' ', $this->status);
    }
}

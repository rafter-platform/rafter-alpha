<?php

namespace App\View\Components;

use App\Environment;
use Illuminate\View\Component;

class EnvironmentSubnav extends Component
{
    public $environment;
    public $project;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(Environment $environment)
    {
        $this->environment = $environment;
        $this->project = $environment->project;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.environment-subnav', [
            'items' => $this->items(),
        ]);
    }

    public function items()
    {
        return [
            [
                'label' => 'Overview',
                'url' => route('projects.environments.show', [$this->project, $this->environment]),
                'exact' => true,
            ],
            'Logs' => route('projects.environments.logs', [$this->project, $this->environment]),
            'Databases' => route('projects.environments.database.index', [$this->project, $this->environment]),
            'Settings' => route('projects.environments.settings.index', [$this->project, $this->environment]),
            'Commands' => route('projects.environments.commands.index', [$this->project, $this->environment]),
        ];
    }
}

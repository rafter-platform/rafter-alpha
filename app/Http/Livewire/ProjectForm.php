<?php

namespace App\Http\Livewire;

use App\GoogleProject;
use Livewire\Component;

class ProjectForm extends Component
{
    public function render()
    {
        return view('livewire.project-form', [
            'projects' => auth()->user()->currentTeam->googleProjects,
            'sourceProviders' => auth()->user()->sourceProviders,
            'regions' => GoogleProject::REGIONS,
        ]);
    }
}

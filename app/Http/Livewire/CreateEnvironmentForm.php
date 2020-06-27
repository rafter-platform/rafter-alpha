<?php

namespace App\Http\Livewire;

use App\Project;
use App\Rules\ValidBranch;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateEnvironmentForm extends Component
{
    use AuthorizesRequests;

    public $project;
    public $name;
    public $branch = 'master';

    public function mount(Project $project)
    {
        $this->project = $project;
    }

    public function render()
    {
        return view('livewire.create-environment-form');
    }

    public function updated()
    {
        return $this->validate($this->rules());
    }

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('environments')->where(function ($query) {
                    return $query->where('project_id', $this->project->id);
                }),
            ],
            'branch' => [
                'required',
                new ValidBranch($this->project->sourceProvider, $this->project->repository),
            ]
        ];
    }

    public function handle()
    {
        $this->authorize('createEnvironments', [$this->project]);

        $this->validate($this->rules());

        $environment = $this->project->environments()->create([
            'name' => $this->name,
            'branch' => $this->branch,
        ]);

        $environment->provision();

        session()->flash('notify', 'Environment successfully created!');

        return redirect()->route('projects.environments.show', [$this->project, $environment]);
    }
}

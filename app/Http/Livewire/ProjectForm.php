<?php

namespace App\Http\Livewire;

use App\GoogleProject;
use App\Project;
use App\Rules\ValidRepository;
use App\Services\GitHub;
use App\SourceProvider;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class ProjectForm extends Component
{
    use WithFileUploads;

    public $repository;
    public $name;
    public $sourceType;
    public $sourceProviderId;
    public $googleProjectId;
    public $type;
    public $region;
    public $serviceAccountJson;
    public $variables;
    public $withDatabase = false;
    public $showGoogleProjectForm = false;
    public $showDatabaseInstanceForm = false;
    public $databaseInstanceId;

    protected $listeners = ['databaseInstanceCreated'];

    public function updated($field)
    {
        $this->validateOnly($field, [
            'sourceProviderId' => ['exists:source_providers,id'],
            'repository' => [new ValidRepository(SourceProvider::find($this->sourceProviderId))],
            'name' => [
                'required',
                Rule::unique('projects')->where(function ($query) {
                    return $query->where('team_id', currentTeam()->id);
                })
            ],
        ]);
    }

    public function updatingRepository($value)
    {
        if (!Str::of($value)->contains('/')) return;

        $this->name = explode('/', $value)[1];
    }

    public function render()
    {
        return view('livewire.project-form', [
            'projects' => currentTeam()->googleProjects,
            'sourceProviders' => auth()->user()->sourceProviders,
            'regions' => GoogleProject::REGIONS,
            'newGitHubInstallationUrl' => GitHub::installationUrl(),
            'databaseInstances' => currentTeam()->databaseInstances,
        ]);
    }

    public function handleOauthCallback($params, $type)
    {
        $query = [];
        parse_str(ltrim($params, '?'), $query);

        switch ($type) {
            case 'github':
                $installationid = $query['installation_id'];

                $source = new SourceProvider([
                    'type' => 'github',
                    'installation_id' => $installationid,
                ]);

                $installation = $source->client()->getInstallation();
                $accountName = explode('/', $installation['repositories'][0])[0];

                $source->meta = $installation;
                $source->name = $accountName;

                auth()->user()->sourceProviders()->save($source);

                $this->sourceProviderId = $source->id;

                break;

            default:
                logger($type . ' not yet supported');
                break;
        }
    }

    public function handleOauthClose()
    {
        auth()->user()
            ->sourceProviders()
            ->whereType('GitHub')
            ->get()
            ->each
            ->refreshGitHubInstallation();
    }

    public function addGoogleProject()
    {
        $this->validate([
            'serviceAccountJson' => 'file|required|mimes:json',
        ]);

        $serviceAccount = json_decode($this->serviceAccountJson->get(), true);

        // Do some cursory validation. May want to expand on this in the future.
        if (empty($serviceAccount['project_id'])) {
            $this->addError('serviceAccountJson', "Please add a valid Google Cloud service account JSON file.");
            return;
        }

        // Enforce uniqueness of project ID
        if (currentTeam()->googleProjects()->whereProjectId($serviceAccount['project_id'])->count()) {
            $this->addError('serviceAccountJson', "A Google Cloud project for {$serviceAccount['project_id']} has already been added.");
            return;
        }

        $project = currentTeam()->googleProjects()->create([
            'project_id' => $serviceAccount['project_id'],
            'service_account_json' => $serviceAccount,
        ]);

        $project->provision();

        $this->reset('serviceAccountJson');
        $this->googleProjectId = $project->id;
        $this->showGoogleProjectForm = false;
    }

    public function create()
    {
        $data = $this->validate([
            'sourceProviderId' => [
                Rule::exists('source_providers', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->user()->id);
                })
            ],
            'googleProjectId' => [
                'required',
                Rule::in(currentTeam()->googleProjects()->pluck('id'))
            ],
            'repository' => [
                'required',
                new ValidRepository(SourceProvider::find($this->sourceProviderId))
            ],
            'name' => [
                'required',
                Rule::unique('projects')->where(function ($query) {
                    return $query->where('team_id', currentTeam()->id);
                })
            ],
            'type' => ['required', Rule::in(array_keys(Project::TYPES))],
            'region' => [
                'required',
                Rule::in(array_keys(GoogleProject::REGIONS)),
            ],
            'variables' => ['nullable', 'string'],
            'databaseInstanceId' => [
                Rule::requiredIf($this->withDatabase),
                'nullable',
                Rule::exists('database_instances', 'id')->where(function ($query) {
                    $query->where('google_project_id', $this->googleProjectId);
                }),
            ],
        ], [
            'type.required' => 'You must select a project type.',
            'googleProjectId.required' => 'You must select a Google Project.',
            'region.required' => 'You must select a region.',
            'name.unique' => 'This name has already been taken by a project on your team. Please choose a different name.',
            'databaseInstanceId.required' => 'You must select a database instance.',
        ]);

        $project = currentTeam()->projects()->create([
            'name' => $data['name'],
            'region' => $data['region'],
            'google_project_id' => $data['googleProjectId'],
            'type' => $data['type'],
            'source_provider_id' => $data['sourceProviderId'],
            'repository' => $data['repository'],
        ]);

        $project->createInitialEnvironments([
            'variables' => $data['variables'],
            'with_database_instance_id' => $data['databaseInstanceId'],
        ]);

        session()->flash('notify', 'Project created!');

        return redirect()->route('projects.show', [$project]);
    }

    public function getSourceProviderProperty()
    {
        return optional(SourceProvider::find($this->sourceProviderId));
    }

    public function databaseInstanceCreated($databaseInstanceId)
    {
        $this->showDatabaseInstanceForm = false;
        $this->databaseInstanceId = $databaseInstanceId;
    }
}

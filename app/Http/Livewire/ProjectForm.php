<?php

namespace App\Http\Livewire;

use App\GoogleProject;
use App\Rules\ValidRepository;
use App\Services\GitHubApp;
use App\SourceProvider;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProjectForm extends Component
{
    use WithFileUploads;

    public $repository;
    public $name;
    public $sourceProviderId;
    public $googleProjectId;
    public $type;
    public $region;
    public $serviceAccountJson;

    public function updated($field)
    {
        $this->validateOnly($field, [
            'sourceProviderId' => ['exists:source_providers,id'],
            'repository' => [new ValidRepository(SourceProvider::find($this->sourceProviderId))],
        ]);
    }

    public function render()
    {
        return view('livewire.project-form', [
            'projects' => auth()->user()->currentTeam->googleProjects,
            'sourceProviders' => auth()->user()->sourceProviders,
            'regions' => GoogleProject::REGIONS,
            'newGitHubInstallationUrl' => GitHubApp::installationUrl(),
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

        $this->serviceAccountJson = '';
        $this->emit('googleProjectAdded', $project->id);
    }

    public function create()
    {
        //
    }

    public function getSourceProviderProperty()
    {
        return optional(SourceProvider::find($this->sourceProviderId));
    }
}

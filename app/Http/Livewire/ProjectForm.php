<?php

namespace App\Http\Livewire;

use App\GoogleProject;
use App\Rules\ValidRepository;
use App\Services\GitHubApp;
use App\SourceProvider;
use Livewire\Component;

class ProjectForm extends Component
{
    public $repository;
    public $sourceProviderId;

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

    public function getSourceProviderProperty()
    {
        return optional(SourceProvider::find($this->sourceProviderId));
    }
}

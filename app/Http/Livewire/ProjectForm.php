<?php

namespace App\Http\Livewire;

use App\GoogleProject;
use App\Services\GitHubApp;
use Livewire\Component;

class ProjectForm extends Component
{
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
        /**
         * TODO:
         * 1. Switch based on type.
         * 2. Parse params
         * 3. If GitHub, SourceProvider::findOrCreateBy installation ID
         * 4. Fetch access token, which then gets us a list of repos and might let us get a name
         * 5. All others, just exchange for OAuth token.
         */
        logger($params);
    }
}

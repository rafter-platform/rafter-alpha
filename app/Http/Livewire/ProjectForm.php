<?php

namespace App\Http\Livewire;

use App\GoogleProject;
use App\Services\GitHubApp;
use App\SourceProvider;
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
        $query = [];
        parse_str(ltrim($params, '?'), $query);

        switch ($type) {
            case 'github':
                $installationid = $query['installation_id'];

                $source = new SourceProvider([
                    'type' => 'GitHub',
                    'installation_id' => $installationid,
                ]);

                $installation = $source->client()->getInstallation();
                $accountName = $installation['repositories'][0]['owner']['login'];

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
        // TODO: Refresh installations for GitHub
        logger('closed');
    }
}

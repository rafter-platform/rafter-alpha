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
        switch ($type) {
                // TODO: Refactor elsewhere
            case 'github':
                $query = [];
                parse_str(ltrim($params, '?'), $query);

                $installationid = $query['installation_id'];
                $installationToken = GitHubApp::getInstallationAccessToken($installationid);
                $token = $installationToken['token'];
                $userToken = GitHubApp::exchangeCodeForAccessToken($query['code']);

                $repositories = GitHubApp::getInstallationRepositories($installationid, $userToken['access_token']);

                $repositories = $repositories['repositories'];
                $accountName = $repositories[0]['owner']['login'];
                $avatar = $repositories[0]['owner']['avatar_url'];

                auth()->user()->sourceProviders()->create([
                    'type' => 'GitHub',
                    'installation_id' => $installationid,
                    'name' => $accountName,
                    'meta' => [
                        'installation_token' => $token,
                        'installation_token_expires_at' => $installationToken['expires_at'],
                        'repositories' => collect($repositories)->map->full_name,
                        'token' => $userToken['access_token'],
                        'avatar' => $avatar,
                    ],
                ]);

                break;

            default:
                logger($type . ' not yet supported');
                break;
        }
    }
}

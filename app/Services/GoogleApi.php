<?php

namespace App\Services;

use App\CloudBuild;
use App\GoogleProject;
use Google_Service_CloudResourceManager;
use GuzzleHttp\Client;

class GoogleApi
{
    protected $googleProject;
    protected $googleClient;

    public function __construct(GoogleProject $googleProject) {
        $this->googleProject = $googleProject;
        $this->googleClient = new \Google_Client();
        $this->googleClient->setAuthConfig($googleProject->service_account_json);
        $this->googleClient->addScope('https://www.googleapis.com/auth/cloud-platform');
    }

    public function token()
    {
        return $this->googleClient->fetchAccessTokenWithAssertion()['access_token'];
    }

    public function getProject()
    {
        return $this->request('https://cloudresourcemanager.googleapis.com/v1/projects/' . $this->googleProject->project_id);
    }

    public function enableApis($apis = [])
    {
        return $this->request(
            "https://serviceusage.googleapis.com/v1/projects/{$this->googleProject->project_number}/services:batchEnable",
            "POST",
            [
                'serviceIds' => $apis,
            ]
        );
    }

    /**
     * Takes a CloudBuild configuration and sends it to Cloud Build to create an image.
     */
    public function createImageForBuild(CloudBuild $cloudBuild)
    {
        return $this->request(
            "https://cloudbuild.googleapis.com/v1/projects/{$this->googleProject->project_id}/builds",
            "POST",
            $cloudBuild->instructions()
        );
    }

    /**
     * Get information about a build.
     */
    public function getBuild($buildId)
    {
        return $this->request(
            "https://cloudbuild.googleapis.com/v1/projects/{$this->googleProject->project_id}/builds/{$buildId}"
        );
    }

    /**
     * Get details about a Cloud Build operation.
     */
    public function getCloudBuildOperation($operationName)
    {
        return $this->request(
            "https://cloudbuild.googleapis.com/v1/{$operationName}"
        );
    }

    protected function request($endpoint, $method = 'GET', $data = [])
    {
        $options = [
            'headers' => [
                'Authorization' => "Bearer {$this->token()}",
            ],
        ];

        if (! empty($data)) {
            $options['json'] = $data;
        }

        $response = (new Client())->request($method, $endpoint, $options);

        return json_decode((string) $response->getBody(), true);
    }
}

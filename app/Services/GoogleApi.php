<?php

namespace App\Services;

use App\CloudBuild;
use App\GoogleProject;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

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

    /**
     * Create a Cloud Run service in a given region.
     */
    public function createCloudRunService($service, $region)
    {
        return $this->request(
            "https://{$region}-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/{$this->googleProject->project_id}/services",
            "POST",
            $service
        );
    }

    /**
     * Get information about a Cloud Run service.
     */
    public function getCloudRunService($name, $region)
    {
        return $this->request(
            "https://{$region}-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/{$this->googleProject->project_id}/services/{$name}"
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

        try {
            $response = (new Client())->request($method, $endpoint, $options);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $exception) {
            dump($exception->getResponse()->getBody()->getContents());

            throw $exception;
        }
    }
}

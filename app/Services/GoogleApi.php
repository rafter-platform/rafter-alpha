<?php

namespace App\Services;

use App\CloudBuild;
use App\Environment;
use App\GoogleCloud\DatabaseInstanceConfig;
use App\GoogleCloud\DatabaseOperation;
use App\GoogleProject;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

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

    /**
     * Get the IAM policy for a given Cloud Run service.
     */
    public function getIamPolicyForCloudRunService(Environment $environment)
    {
        return $this->request($this->cloudRunIamPolicyUrl($environment) . ':getIamPolicy');
    }

    /**
     * Get the IAM policy for a given Cloud Run service.
     */
    public function setIamPolicyForCloudRunService(Environment $environment, $policy)
    {
        return $this->request(
            $this->cloudRunIamPolicyUrl($environment) . ':setIamPolicy',
            "POST",
            [
                'policy' => $policy,
            ]
        );
    }

    /**
     * Get the URL to interact with an environment's Cloud Run IAM policy, which is... really long.
     */
    protected function cloudRunIamPolicyUrl(Environment $environment)
    {
        return sprintf(
            "https://%s-run.googleapis.com/v1/projects/%s/locations/%s/services/%s",
            $environment->project->region,
            $environment->project->googleProject->project_id,
            $environment->project->region,
            $environment->slug()
        );
    }

    /**
     * Create a Database Instance on Google Cloud.
     */
    public function createDatabaseInstance(DatabaseInstanceConfig $databaseInstanceConfig)
    {
        return $this->request(
            "https://www.googleapis.com/sql/v1beta4/projects/{$databaseInstanceConfig->projectId()}/instances",
            "POST",
            $databaseInstanceConfig->config()
        );
    }

    /**
     * Get a current database operation.
     */
    public function getDatabaseOperation($projectId, $operationName)
    {
        $response = $this->request("https://www.googleapis.com/sql/v1beta4/projects/{$projectId}/operations/{$operationName}");

        return new DatabaseOperation($response);
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

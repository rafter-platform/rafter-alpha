<?php

namespace App\Services;

use App\DatabaseInstance;
use App\Environment;
use App\GoogleCloud\CloudBuildConfig;
use App\GoogleCloud\CloudBuildOperation;
use App\GoogleCloud\CloudRunConfig;
use App\GoogleCloud\CloudRunIamPolicy;
use App\GoogleCloud\CloudRunService;
use App\GoogleCloud\DatabaseConfig;
use App\GoogleCloud\DatabaseInstanceConfig;
use App\GoogleCloud\DatabaseOperation;
use App\GoogleCloud\EnableApisOperation;
use App\GoogleCloud\QueueConfig;
use App\GoogleCloud\SchedulerJobConfig;
use App\GoogleProject;
use Google_Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleApi
{
    protected $googleProject;
    protected $googleClient;

    public function __construct(GoogleProject $googleProject) {
        $this->googleProject = $googleProject;
        $this->googleClient = app(Google_Client::class);
        $this->googleClient->setAuthConfig($googleProject->service_account_json);
        $this->googleClient->addScope('https://www.googleapis.com/auth/cloud-platform');
    }

    /**
     * Get a Google Cloud Project
     *
     * @return array
     */
    public function getProject()
    {
        return $this->request('https://cloudresourcemanager.googleapis.com/v1/projects/' . $this->googleProject->project_id);
    }

    /**
     * Enable a given set of APIs
     *
     * @param array $apis
     * @return array
     */
    public function enableApis($apis = [])
    {
        return $this->request(
            "https://serviceusage.googleapis.com/v1/projects/{$this->googleProject->project_id}/services:batchEnable",
            "POST",
            [
                'serviceIds' => $apis,
            ]
        );
    }

    public function getEnableApisOperation($operationName)
    {
        $response = $this->request("https://serviceusage.googleapis.com/v1/{$operationName}");

        return new EnableApisOperation($response);
    }

    /**
     * Whether this project has an app engine app associated yet.
     *
     * @return boolean
     */
    public function hasAppEngineApp()
    {
        try {
            $res = $this->request(
                "https://appengine.googleapis.com/v1/apps/{$this->googleProject->project_id}"
            );

            return true;
        } catch (ClientException $e) {
            return false;
        }
    }

    /**
     * Create a shell AppEngine app to be able to use Cloud Tasks.
     * Hopefully temporary.
     *
     * @return array
     */
    public function createAppEngineApp()
    {
        return $this->request(
            "https://appengine.googleapis.com/v1/apps",
            "POST",
            [
                'id' => $this->googleProject->project_id,
                'locationId' => 'us-central',
            ]
        );
    }

    /**
     * Takes a CloudBuild configuration and sends it to Cloud Build to create an image.
     */
    public function createImageForBuild(CloudBuildConfig $cloudBuild)
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
        $response = $this->request(
            "https://cloudbuild.googleapis.com/v1/{$operationName}"
        );

        return new CloudBuildOperation($response);
    }

    /**
     * Create a Cloud Run service in a given region.
     */
    public function createCloudRunService(CloudRunConfig $cloudRunConfig)
    {
        return $this->request(
            "https://{$cloudRunConfig->region()}-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/{$cloudRunConfig->projectId()}/services",
            "POST",
            $cloudRunConfig->config()
        );
    }

    /**
     * Replace a revision on a Cloud Run service (aka deploy a new image).
     */
    public function replaceCloudRunService(CloudRunConfig $cloudRunConfig)
    {
        return $this->request(
            "https://{$cloudRunConfig->region()}-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/{$cloudRunConfig->projectId()}/services/{$cloudRunConfig->name()}",
            "PUT",
            $cloudRunConfig->config()
        );
    }

    /**
     * Get information about a Cloud Run service.
     */
    public function getCloudRunService($name, $region)
    {
        $response = $this->request(
            "https://{$region}-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/{$this->googleProject->project_id}/services/{$name}"
        );

        return new CloudRunService($response);
    }

    /**
     * Get the IAM policy for a given Cloud Run web service.
     */
    public function getIamPolicyForCloudRunService(Environment $environment): CloudRunIamPolicy
    {
        $response = $this->request($this->cloudRunIamPolicyUrl($environment) . ':getIamPolicy');

        return new CloudRunIamPolicy($response);
    }

    /**
     * Get the IAM policy for a given Cloud Run web service.
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
     * Get the URL to interact with a web environment's Cloud Run IAM policy, which is... really long.
     */
    protected function cloudRunIamPolicyUrl(Environment $environment)
    {
        return sprintf(
            "https://%s-run.googleapis.com/v1/projects/%s/locations/%s/services/%s",
            $environment->project->region,
            $environment->project->googleProject->project_id,
            $environment->project->region,
            $environment->web_service_name
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
     * Return a list of instances in this Google Project
     *
     * @return array
     */
    public function getDatabaseInstances()
    {
        return $this->request("https://www.googleapis.com/sql/v1beta4/projects/{$this->googleProject->project_id}/instances");
    }

    /**
     * Get a current database operation.
     *
     * @return \App\GoogleCloud\DatabaseOperation
     */
    public function getDatabaseOperation($projectId, $operationName)
    {
        $response = $this->request("https://www.googleapis.com/sql/v1beta4/projects/{$projectId}/operations/{$operationName}");

        return new DatabaseOperation($response);
    }

    /**
     * Create a database.
     *
     * @param DatabaseConfig $databaseConfig
     * @return array
     */
    public function createDatabase(DatabaseConfig $databaseConfig)
    {
        return $this->request(
            "https://www.googleapis.com/sql/v1beta4/projects/{$databaseConfig->projectId()}/instances/{$databaseConfig->instanceName()}/databases",
            "POST",
            $databaseConfig->config()
        );
    }

    /**
     * Get databases in a database instance
     *
     * @param DatabaseInstance $databaseInstance
     * @return array
     */
    public function getDatabases(DatabaseInstance $databaseInstance)
    {
        return $this->request(
            "https://www.googleapis.com/sql/v1beta4/projects/{$this->googleProject->project_id}/instances/{$databaseInstance->name}/databases"
        );
    }

    /**
     * Create or update a queue
     *
     * @param QueueConfig $queueConfig
     * @return array
     */
    public function createOrUpdateQueue(QueueConfig $queueConfig)
    {
        $this->request(
            "https://cloudtasks.googleapis.com/v2beta3/{$queueConfig->name()}",
            "PATCH",
            $queueConfig->config()
        );
    }

    /**
     * Create a Google Cloud Scheduler job
     *
     * @param SchedulerJobConfig $schedulerJobConfig
     * @return array
     */
    public function createSchedulerJob(SchedulerJobConfig $schedulerJobConfig)
    {
        $this->request(
            "https://cloudscheduler.googleapis.com/v1/projects/{$schedulerJobConfig->projectId()}/locations/{$schedulerJobConfig->location()}/jobs",
            "POST",
            $schedulerJobConfig->config()
        );
    }

    /**
     * Request data from the Google Cloud API.
     *
     * @param string $endpoint
     * @param string $method
     * @param array $data
     * @return array
     */
    protected function request($endpoint, $method = 'GET', $data = [])
    {
        try {
            return Http::withToken($this->token())
                ->{$method}($endpoint, $data)
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            Log::error($exception->response->body());

            throw $exception;
        }
    }

    /**
     * Get an access token for the given service account.
     *
     * @return string
     */
    protected function token()
    {
        return $this->googleClient->fetchAccessTokenWithAssertion()['access_token'];
    }
}

<?php

namespace Tests\Feature;

use App\EnvVars;
use App\Jobs\CreateCloudRunService;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google_Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakeGoogleApiClient;
use Tests\Support\FakeGoogleSecretManagerClient;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);
        $this->app->bind(SecretManagerServiceClient::class, function () {
            return new FakeGoogleSecretManagerClient;
        });

        $this->mockGitHubForDeployment();
    }

    public function test_initial_deployment_works_as_expected()
    {
        Http::fake([
            // CreateImageForDeployment
            'cloudbuild.googleapis.com/v1/projects/*/builds' => Http::response(['name' => 'bar'], 200),

            // WaitForImageToBeBuilt
            'cloudbuild.googleapis.com/v1/bar' => Http::response([
                'done' => true,
                'metadata' => [
                    'build' => [
                        'id' => 'foo',
                    ],
                ],
            ], 200),

            'cloudbuild.googleapis.com/v1/projects/*/builds/foo' => Http::response([
                'results' => [
                    'images' => [
                        [
                            'name' => 'some/build',
                            'digest' => 'foo',
                        ],
                    ],
                ],
            ], 200),

            // WaitForCloudRunServiceToDeploy
            'us-central1-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/*/services/*' => Http::response($this->loadStub('cloud-run-service'), 200),

            // EnsureAppIsPublic
            'https://us-central1-run.googleapis.com/v1/projects/*/locations/*/services/*:getIamPolicy' => Http::response([
                'bindings' => [
                    [
                        'role' => 'roles/run.invoker',
                        'members' => [
                            'allUsers',
                        ],
                    ],
                ],
            ], 200),

            // Stub a string response for all other endpoints...
            '*' => Http::response('Hello World', 200, ['Headers']),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $deployment = $environment->createInitialDeployment();

        $environment->refresh();
        $deployment->refresh();

        $this->assertSame('successful', $deployment->status);
        $this->assertTrue($environment->activeDeployment->is($deployment));

        $steps = [
            'StartDeployment',
            'WaitForGoogleProjectToBeProvisioned',
            'SetBuildSecrets',
            'CreateImageForDeployment',
            'ConfigureQueues',
            'WaitForImageToBeBuilt',
            'CreateCloudRunService',
            'WaitForCloudRunServiceToDeploy',
            'UpdateCloudRunServiceWithUrls',
            'WaitForCloudRunServiceToDeploy',
            'EnsureAppIsPublic',
            'StartScheduler',
            'FinalizeDeployment',
        ];

        $this->assertCount(count($steps), $deployment->steps);

        foreach ($steps as $step) {
            $deploymentStep = $deployment->steps()->where('name', $step)->first();
            $this->assertTrue($deploymentStep->exists());
            $this->assertTrue($deploymentStep->hasFinished());
        }
    }

    public function test_deployment_is_marked_as_failed_if_a_job_fails()
    {
        Http::fake([
            // CreateImageForDeployment
            'cloudbuild.googleapis.com/v1/projects/*/builds' => Http::response(['something' => 'unexpected'], 200),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $deployment = $environment->createInitialDeployment();

        $deployment->refresh();

        $this->assertSame('failed', $deployment->status);
    }

    public function test_standard_deployment_works_as_expected()
    {
        Http::fake([
            // CreateImageForDeployment
            'cloudbuild.googleapis.com/v1/projects/*/builds' => Http::response(['name' => 'bar'], 200),

            // WaitForImageToBeBuilt
            'cloudbuild.googleapis.com/v1/bar' => Http::response([
                'done' => true,
                'metadata' => [
                    'build' => [
                        'id' => 'foo',
                    ],
                ],
            ], 200),

            'cloudbuild.googleapis.com/v1/projects/*/builds/foo' => Http::response([
                'results' => [
                    'images' => [
                        [
                            'name' => 'some/build',
                            'digest' => 'foo',
                        ],
                    ],
                ],
            ], 200),

            // WaitForCloudRunServiceToDeploy
            'us-central1-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/*/services/*' => Http::response($this->loadStub('cloud-run-service'), 200),

            // Stub a string response for all other endpoints...
            '*' => Http::response('Hello World', 200, ['Headers']),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $deployment = $environment->deployHash('abc123', null);

        $environment->refresh();
        $deployment->refresh();

        $this->assertSame('successful', $deployment->status);
        $this->assertTrue($environment->activeDeployment->is($deployment));

        $steps = [
            'StartDeployment',
            'SetBuildSecrets',
            'CreateImageForDeployment',
            'ConfigureQueues',
            'WaitForImageToBeBuilt',
            'UpdateCloudRunService',
            'WaitForCloudRunServiceToDeploy',
            'FinalizeDeployment',
        ];

        $this->assertCount(count($steps), $deployment->steps);

        foreach ($steps as $step) {
            $deploymentStep = $deployment->steps()->where('name', $step)->first();
            $this->assertTrue($deploymentStep->exists());
            $this->assertTrue($deploymentStep->hasFinished());
        }
    }

    public function test_deployment_can_be_redeployed()
    {
        Http::fake([
            // CreateImageForDeployment
            'cloudbuild.googleapis.com/v1/projects/*/builds' => Http::response(['name' => 'bar'], 200),

            // WaitForImageToBeBuilt
            'cloudbuild.googleapis.com/v1/bar' => Http::response([
                'done' => true,
                'metadata' => [
                    'build' => [
                        'id' => 'foo',
                    ],
                ],
            ], 200),

            'cloudbuild.googleapis.com/v1/projects/*/builds/foo' => Http::response([
                'results' => [
                    'images' => [
                        [
                            'name' => 'some/build',
                            'digest' => 'foo',
                        ],
                    ],
                ],
            ], 200),

            // WaitForCloudRunServiceToDeploy
            'us-central1-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/*/services/*' => Http::response($this->loadStub('cloud-run-service'), 200),

            // Stub a string response for all other endpoints...
            '*' => Http::response('Hello World', 200, ['Headers']),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $deployment = $environment->deployHash('abc123', null);

        $environment->refresh();
        $deployment->refresh();

        $this->assertSame('successful', $deployment->status);
        $this->assertTrue($environment->activeDeployment->is($deployment));

        $newDeployment = $deployment->redeploy();

        $steps = [
            'StartDeployment',
            'ConfigureQueues',
            'UpdateCloudRunService',
            'WaitForCloudRunServiceToDeploy',
            'FinalizeDeployment',
        ];

        $this->assertCount(count($steps), $newDeployment->steps);

        foreach ($steps as $step) {
            $deploymentStep = $newDeployment->steps()->where('name', $step)->first();
            $this->assertTrue($deploymentStep->exists());
            $this->assertTrue($deploymentStep->hasFinished());
        }
    }

    public function test_redeploy_performs_initial_deploy_if_no_successful_deployment_exists()
    {
        Http::fake([
            // CreateImageForDeployment
            'cloudbuild.googleapis.com/v1/projects/*/builds' => Http::response(['name' => 'bar'], 200),

            // WaitForImageToBeBuilt
            'cloudbuild.googleapis.com/v1/bar' => Http::response([
                'done' => true,
                'metadata' => [
                    'build' => [
                        'id' => 'foo',
                    ],
                ],
            ], 200),

            'cloudbuild.googleapis.com/v1/projects/*/builds/foo' => Http::response([
                'results' => [
                    'images' => [
                        [
                            'name' => 'some/build',
                            'digest' => 'foo',
                        ],
                    ],
                ],
            ], 200),

            // WaitForCloudRunServiceToDeploy
            'us-central1-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/*/services/*' => Http::response($this->loadStub('cloud-run-service'), 200),

            // EnsureAppIsPublic
            'https://us-central1-run.googleapis.com/v1/projects/*/locations/*/services/*:getIamPolicy' => Http::response([
                'bindings' => [
                    [
                        'role' => 'roles/run.invoker',
                        'members' => [
                            'allUsers',
                        ],
                    ],
                ],
            ], 200),

            // Stub a string response for all other endpoints...
            '*' => Http::response('Hello World', 200, ['Headers']),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $failedDeployment = factory('App\Deployment')->create([
            'environment_id' => $environment->id,
            'status' => 'failed',
        ]);

        $deployment = $failedDeployment->redeploy();

        $steps = [
            'StartDeployment',
            'WaitForGoogleProjectToBeProvisioned',
            'SetBuildSecrets',
            'CreateImageForDeployment',
            'ConfigureQueues',
            'WaitForImageToBeBuilt',
            'CreateCloudRunService',
            'WaitForCloudRunServiceToDeploy',
            'UpdateCloudRunServiceWithUrls',
            'WaitForCloudRunServiceToDeploy',
            'EnsureAppIsPublic',
            'StartScheduler',
            'FinalizeDeployment',
        ];

        $this->assertCount(count($steps), $deployment->steps);
        $this->assertSame($steps, $deployment->steps()->pluck('name')->toArray());
    }

    public function test_cloud_run_service_is_updated_instead_of_created_on_initial_deployment_if_already_exists()
    {
        Http::fake([
            // The first attempt to create a new service will fail
            'us-central1-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/*/services' => Http::response(['error' => ['status' => 'ALREADY_EXISTS']], 409),

            /**
             * The next attempts to
             * 1) get the current service and
             * 2) update the service
             * will simply get the service in response.
             */
            'us-central1-run.googleapis.com/apis/serving.knative.dev/v1/namespaces/*/services/*' => Http::response($this->loadStub('cloud-run-service')),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $this->assertEquals('', $environment->environmental_variables);
        $this->assertEquals('', $environment->web_service_name);
        $this->assertEquals('', $environment->worker_service_name);

        $deployment = $environment->deployments()->create([
            'commit_hash' => 'abc123',
            'commit_message' => 'Initial Deploy',
        ]);

        CreateCloudRunService::dispatchNow($deployment);

        // Ensure the vars from the existing service are re-used
        $this->assertEquals([
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => '/var/www/database/database.sqlite',
        ], EnvVars::fromString($environment->refresh()->environmental_variables)->get());

        // Ensure the web and worker values are now set
        $this->assertEquals($environment->slug(), $environment->web_service_name);
        $this->assertEquals($environment->slug() . '-worker', $environment->worker_service_name);
    }
}

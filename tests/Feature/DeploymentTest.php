<?php

namespace Tests\Feature;

use Google_Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakeGoogleApiClient;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_deployment_works_as_expected()
    {
        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

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

        $this->assertEquals('successful', $deployment->status);
        $this->assertTrue($environment->activeDeployment->is($deployment));

        $steps = [
            'StartDeployment',
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
        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

        Http::fake([
            // CreateImageForDeployment
            'cloudbuild.googleapis.com/v1/projects/*/builds' => Http::response(['something' => 'unexpected'], 200),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $deployment = $environment->createInitialDeployment();

        $deployment->refresh();

        $this->assertEquals('failed', $deployment->status);
    }

    public function test_standard_deployment_works_as_expected()
    {
        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

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

        $deployment = $environment->deploy('abc123', 'commit message', null);

        $environment->refresh();
        $deployment->refresh();

        $this->assertEquals('successful', $deployment->status);
        $this->assertTrue($environment->activeDeployment->is($deployment));

        $steps = [
            'StartDeployment',
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
        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

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

        $deployment = $environment->deploy('abc123', 'commit message', null);

        $environment->refresh();
        $deployment->refresh();

        $this->assertEquals('successful', $deployment->status);
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
        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

        $environment = factory('App\Environment')->state('laravel')->create();

        $failedDeployment = factory('App\Deployment')->create([
            'environment_id' => $environment->id,
            'status' => 'failed',
        ]);

        $deployment = $failedDeployment->redeploy();

        $steps = [
            'StartDeployment',
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
        $this->assertEquals($steps, $deployment->steps()->pluck('name')->toArray());
    }
}

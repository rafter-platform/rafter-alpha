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

            // TODO: Add other HTTP responses to ensure we get all the way to a successful deployment

            // Stub a string response for all other endpoints...
            '*' => Http::response('Hello World', 200, ['Headers']),
        ]);

        $environment = factory('App\Environment')->state('laravel')->create();

        $deployment = $environment->createInitialDeployment();

        $deployment->refresh();

        $this->assertEquals('in_progress', $deployment->status);
    }
}

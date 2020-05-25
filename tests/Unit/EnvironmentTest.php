<?php

namespace Tests\Unit;

use App\Jobs\ConfigureQueues;
use App\Jobs\CreateCloudRunService;
use App\Jobs\CreateImageForDeployment;
use App\Jobs\EnsureAppIsPublic;
use App\Jobs\FinalizeDeployment;
use App\Jobs\SetBuildSecrets;
use App\Jobs\StartDeployment;
use App\Jobs\StartScheduler;
use App\Jobs\UpdateCloudRunServiceWithUrls;
use App\Jobs\WaitForCloudRunServiceToDeploy;
use App\Jobs\WaitForImageToBeBuilt;
use Google_Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\FakeGoogleApiClient;
use Tests\TestCase;
use App\DomainMapping;
use App\Environment;

class EnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_initial_deployment_enqueues_expected_jobs()
    {
        Queue::fake();

        $environment = factory(Environment::class)->state('laravel')->create();

        $environment->createInitialDeployment();

        Queue::assertPushedWithChain(StartDeployment::class, [
            SetBuildSecrets::class,
            CreateImageForDeployment::class,
            ConfigureQueues::class,
            WaitForImageToBeBuilt::class,
            CreateCloudRunService::class,
            WaitForCloudRunServiceToDeploy::class,
            UpdateCloudRunServiceWithUrls::class,
            WaitForCloudRunServiceToDeploy::class,
            EnsureAppIsPublic::class,
            StartScheduler::class,
            FinalizeDeployment::class,
        ]);
    }

    public function test_create_initial_deployment_enqueues_expected_jobs_for_nodejs()
    {
        $environment = factory(Environment::class)->state('nodejs')->create();

        Queue::fake();

        $environment->createInitialDeployment();

        Queue::assertPushedWithChain(StartDeployment::class, [
            SetBuildSecrets::class,
            CreateImageForDeployment::class,
            ConfigureQueues::class,
            WaitForImageToBeBuilt::class,
            CreateCloudRunService::class,
            WaitForCloudRunServiceToDeploy::class,
            UpdateCloudRunServiceWithUrls::class,
            WaitForCloudRunServiceToDeploy::class,
            EnsureAppIsPublic::class,
            FinalizeDeployment::class,
        ]);
    }

    public function test_scheduler_is_started()
    {
        Http::fake();

        $this->app->instance(Google_Client::class, new FakeGoogleApiClient);

        $environment = factory(Environment::class)->state('laravel')->create([
            'worker_url' => 'https://some.a.run.app',
        ]);

        $environment->startScheduler();

        Http::assertSent(function ($request) use ($environment) {
            return $request->method() === 'POST'
                && $request->url() == "https://cloudscheduler.googleapis.com/v1/projects/{$environment->projectId()}/locations/{$environment->region()}/jobs"
                && $request['name'] == "projects/{$environment->projectId()}/locations/{$environment->region()}/jobs/{$environment->slug()}-run-schedule"
                && $request['schedule'] == '* * * * *'
                && $request['httpTarget']['uri'] == $environment->worker_url . '/_rafter/schedule/run'
                && $request['httpTarget']['httpMethod'] == 'POST'
                && $request['httpTarget']['oidcToken']['serviceAccountEmail'] == 'rafter@rafter.service.account.com';
        });
    }

    public function test_primary_domain_is_known()
    {
        $environment = factory(Environment::class)->state('laravel')->create([
            'url' => 'https://some.a.run.app',
        ]);

        $this->assertEquals('some.a.run.app', $environment->primaryDomain());

        // Throw in an inactive mapping to ensure it doesn't count as a primary domain
        $environment->domainMappings()->create(factory(DomainMapping::class)->raw([
            'status' => 'inactive',
        ]));

        $environment->refresh();

        $this->assertEquals('some.a.run.app', $environment->primaryDomain());

        $mapping = $environment->domainMappings()->create(factory(DomainMapping::class)->raw([
            'status' => 'active',
        ]));

        $environment->refresh();

        $this->assertEquals($mapping->domain, $environment->primaryDomain());
    }

    public function test_knows_additional_domains()
    {
        $environment = factory(Environment::class)->state('laravel')->create([
            'url' => 'https://some.a.run.app',
        ]);

        $this->assertEquals(0, $environment->additionalDomainsCount());

        $environment->domainMappings()->createMany(factory(DomainMapping::class, 5)->raw([
            'status' => 'active',
        ]));

        $environment->refresh();

        $this->assertEquals(5, $environment->additionalDomainsCount());
    }
}

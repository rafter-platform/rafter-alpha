<?php

namespace Tests\Unit;

use App\Jobs\ConfigureQueues;
use App\Jobs\CreateCloudRunService;
use App\Jobs\CreateImageForDeployment;
use App\Jobs\EnsureAppIsPublic;
use App\Jobs\FinalizeDeployment;
use App\Jobs\StartDeployment;
use App\Jobs\StartScheduler;
use App\Jobs\UpdateCloudRunServiceWithUrls;
use App\Jobs\WaitForCloudRunServiceToDeploy;
use App\Jobs\WaitForImageToBeBuilt;
use App\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_initial_deployment_enqueues_expected_jobs()
    {
        Queue::fake();

        $environment = factory('App\Environment')->create([
            'project_id' => factory('App\Project')
                ->state('laravel')
                ->create()
                ->id,
        ]);

        $environment->createInitialDeployment();

        Queue::assertPushedWithChain(StartDeployment::class, [
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
        $environment = factory('App\Environment')->create([
            'project_id' => factory('App\Project')
                ->state('nodejs')
                ->create()
                ->id,
        ]);

        Queue::fake();

        $environment->createInitialDeployment();

        Queue::assertPushedWithChain(StartDeployment::class, [
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
}

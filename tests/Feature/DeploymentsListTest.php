<?php

namespace Tests\Feature;

use App\Deployment;
use App\Environment;
use App\Http\Livewire\DeploymentsList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class DeploymentsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_deployments_are_listed()
    {
        $environment = factory(Environment::class)->create();
        $deployments = factory(Deployment::class, 5)->create([
            'environment_id' => $environment,
        ]);

        // Pretend second deployment is a redeploy of the first
        $deployments[1]->redeployment()->associate($deployments[0]);
        $deployments[1]->save();

        Livewire::test(DeploymentsList::class, ['environment' => $environment])
            ->assertOk()
            ->assertSee($deployments[0]->commit_message)
            ->assertSee("Redeploy of Deployment #{$deployments[0]->id}");
    }
}

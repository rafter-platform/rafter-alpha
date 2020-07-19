<?php

namespace Tests\Feature;

use App\Http\Livewire\CreateEnvironmentForm;
use App\Jobs\StartDeployment;
use App\Project;
use App\Services\GitHub;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class CreateEnvironmentFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_environment_in_their_project()
    {
        Queue::fake();

        $mock = GitHub::partialMock();
        $mock->shouldReceive('validRepository')->andReturns(true);
        $mock->shouldReceive('latestHashFor')->andReturn('abc123');
        $mock->shouldReceive('createDeployment')->andReturn(['id' => 123]);
        $mock->shouldReceive('updateDeploymentStatus');

        $project = factory(Project::class)->create();
        $user = $project->team->owner;
        $user->setCurrentTeam($project->team);

        Livewire::actingAs($user->refresh())
            ->test(CreateEnvironmentForm::class, ['project' => $project])
            ->set('name', 'Environment Name')
            ->set('branch', 'test')
            ->call('handle')
            ->assertOk();

        $this->assertCount(1, $project->refresh()->environments);
        $this->assertEquals('Environment Name', $project->environments->first()->name);
        $this->assertEquals('test', $project->environments->first()->branch);

        Queue::assertPushed(StartDeployment::class);
    }

    public function test_user_cannot_create_environment_with_same_name_as_existing_in_project()
    {
        $mock = GitHub::partialMock();
        $mock->shouldReceive('validRepository')->andReturns(true);

        $project = factory(Project::class)->create();
        $project->environments()->create(['name' => 'Existing']);
        $user = $project->team->owner;
        $user->setCurrentTeam($project->team);

        Livewire::actingAs($user)
            ->test(CreateEnvironmentForm::class, ['project' => $project])
            ->set('name', 'Existing')
            ->assertHasErrors(['name' => 'unique']);
    }

    public function test_user_cannot_create_environments_in_someone_elses_project()
    {
        $mock = GitHub::partialMock();
        $mock->shouldReceive('validRepository')->andReturns(true);

        $project = factory(Project::class)->create();
        $project->environments()->create(['name' => 'Existing']);
        $randomUser = factory(User::class)->create();
        event(new Registered($randomUser));

        Livewire::actingAs($randomUser)
            ->test(CreateEnvironmentForm::class, ['project' => $project])
            ->set('name', 'Environment Name')
            ->set('branch', 'master')
            ->call('handle')
            ->assertForbidden();
    }
}

<?php

namespace Tests\Unit;

use App\Events\UserRegistered;
use App\GoogleProject;
use App\Http\Livewire\ProjectForm;
use App\Jobs\StartDeployment;
use App\SourceProvider;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_required_fields()
    {
        $this->actingAs(factory(User::class)->create());

        Livewire::test(ProjectForm::class)
            ->call('create')
            ->assertHasErrors([
                'repository' => 'required',
                'name' => 'required',
                'googleProjectId' => 'required',
                'region' => 'required',
                'type' => 'required',
            ]);
    }

    public function test_it_creates_a_project()
    {
        $user = factory(User::class)->create();
        UserRegistered::dispatch($user);
        $user->refresh();

        Queue::fake();

        Livewire::actingAs($user)
            ->test(ProjectForm::class)
            ->set('sourceType', 'github')
            ->set('sourceProviderId', factory(SourceProvider::class)->create([
                'user_id' => $user->id
            ])->id)
            ->set('repository', 'some/repo')
            ->set('name', 'repo')
            ->set('type', 'laravel')
            ->set('googleProjectId', factory(GoogleProject::class)->create([
                'team_id' => $user->currentTeam->id,
            ])->id)
            ->set('region', 'us-central1')
            ->set('variables', 'SOME=thing')
            ->call('create');

        $user->refresh();

        $this->assertCount(1, $user->currentTeam->projects);
        $project = $user->currentTeam->projects->first();

        $this->assertEquals('repo', $project->name);
        $this->assertEquals('some/repo', $project->repository);
        $this->assertEquals('laravel', $project->type);
        $this->assertEquals('us-central1', $project->region);

        $environment = $project->environments->first();
        $this->assertEquals('thing', $environment->getEnvVar('SOME'));

        Queue::assertPushed(StartDeployment::class);
    }

    public function test_it_creates_a_project_with_no_variables()
    {
        $user = factory(User::class)->create();
        UserRegistered::dispatch($user);
        $user->refresh();

        Queue::fake();

        Livewire::actingAs($user)
            ->test(ProjectForm::class)
            ->set('sourceType', 'github')
            ->set('sourceProviderId', factory(SourceProvider::class)->create([
                'user_id' => $user->id
            ])->id)
            ->set('repository', 'some/repo')
            ->set('name', 'repo')
            ->set('type', 'laravel')
            ->set('googleProjectId', factory(GoogleProject::class)->create([
                'team_id' => $user->currentTeam->id,
            ])->id)
            ->set('region', 'us-central1')
            ->call('create');

        $user->refresh();

        $this->assertCount(1, $user->currentTeam->projects);
        $project = $user->currentTeam->projects->first();

        $this->assertEquals('repo', $project->name);
        $this->assertEquals('some/repo', $project->repository);
        $this->assertEquals('laravel', $project->type);
        $this->assertEquals('us-central1', $project->region);

        Queue::assertPushed(StartDeployment::class);
    }
}

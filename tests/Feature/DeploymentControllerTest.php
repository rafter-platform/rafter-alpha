<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Deployment;
use App\Environment;
use App\Project;
use App\User;

class DeploymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->other = factory(Deployment::class)->create();
    }

    public function test_user_can_view_their_deployments()
    {
        $project = factory(Project::class)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory(Environment::class)->create([
            'project_id' => $project->id,
        ]);

        $deployments = factory(Deployment::class, 3)->create([
            'environment_id' => $environment->id,
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get(route('projects.environments.show', [$project, $environment]))
            ->assertSuccessful();

        $deployments->each(function ($deployment) use ($response) {
            $response->assertSee($deployment->commit_message);
        });

        $response->assertDontSee($this->other->commit_message);

        $this->get(route('projects.environments.deployments.show', [$project, $environment, $deployments->first()]))
            ->assertSuccessful();

        $this->get(route('projects.environments.deployments.show', [
            $this->other->environment->project,
            $this->other->environment,
            $this->other
        ]))
            ->assertForbidden();
    }
}

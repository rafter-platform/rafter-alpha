<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DeploymentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->other = factory('App\Deployment')->create();
    }

    public function test_user_can_view_their_deployments()
    {
        $project = factory('App\Project')->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory('App\Environment')->create([
            'project_id' => $project->id,
        ]);

        $deployments = factory('App\Deployment', 3)->create([
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

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EnvironmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->other = factory('App\Environment')->create();
    }

    public function test_user_can_view_their_environments()
    {
        $project = factory('App\Project')->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory('App\Environment')->create([
            'project_id' => $project->id,
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get(route('projects.environments.show', [$project, $environment]))
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get(route('projects.environments.show', [$this->other->project, $this->other]))
            ->assertForbidden();
    }
}

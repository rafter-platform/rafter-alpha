<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Environment;
use App\Project;
use App\User;

class EnvironmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->other = factory(Environment::class)->create();
    }

    public function test_user_can_view_their_environments()
    {
        $project = factory(Project::class)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory(Environment::class)->create([
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

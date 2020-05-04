<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommandControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->other = factory('App\Command')->create();
    }

    public function test_user_can_view_their_commands()
    {
        $project = factory('App\Project')->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory('App\Environment')->create([
            'project_id' => $project->id,
        ]);

        $commands = factory('App\Command', 3)->create([
            'environment_id' => $environment->id,
            'user_id' => $this->user->id,
        ]);

        // Also ensure command created by another user on the same team is visible
        $otherTeamMember = factory('App\User')->create();
        $this->user->currentTeam->users()->attach($otherTeamMember);

        $otherCommandOnTeam = factory('App\Command')->create([
            'environment_id' => $environment->id,
            'user_id' => $otherTeamMember->id,
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get(route('projects.environments.commands.index', [$project, $environment]))
            ->assertSuccessful();

        $commands->each(function ($command) use ($response) {
            $response->assertSee($command->command);
        });

        $response->assertSee($otherCommandOnTeam->command);

        $response->assertDontSee($this->other->command);

        $this->get(route('projects.environments.commands.show', [$project, $environment, $commands->first()]))
            ->assertSuccessful();

        $this->get(route('projects.environments.commands.show', [
            $this->other->environment->project,
            $this->other->environment,
            $this->other
        ]))
            ->assertForbidden();
    }
}

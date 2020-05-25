<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Command;
use App\User;
use App\Environment;
use App\Project;

class CommandControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->other = factory(Command::class)->create();
    }

    public function test_user_can_view_their_commands()
    {
        $project = factory(Project::class)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory(Environment::class)->create([
            'project_id' => $project->id,
        ]);

        $commands = factory(Command::class, 3)->create([
            'environment_id' => $environment->id,
            'user_id' => $this->user->id,
        ]);

        // Also ensure command created by another user on the same team is visible
        $otherTeamMember = factory(User::class)->create();
        $this->user->currentTeam->users()->attach($otherTeamMember);

        $otherCommandOnTeam = factory(Command::class)->create([
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

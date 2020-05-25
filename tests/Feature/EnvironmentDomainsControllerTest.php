<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\User;
use App\DomainMapping;
use App\Environment;
use App\Project;

class EnvironmentDomainsController extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->other = factory(User::class)->create();
    }

    public function test_user_can_view_their_domains()
    {
        $project = factory(Project::class)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $environment = factory(Environment::class)->create([
            'project_id' => $project->id,
        ]);

        $domains = factory(DomainMapping::class, 3)->create([
            'environment_id' => $environment->id,
        ]);

        // Also ensure domain created by another user on the same team is visible
        $otherTeamMember = factory(User::class)->create();
        $this->user->currentTeam->users()->attach($otherTeamMember);
        $otherTeamMember->setCurrentTeam($this->user->currentTeam);
        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get(route('projects.environments.domains', [$project, $environment]))
            ->assertSuccessful();

        $domains->each(function ($domain) use ($response) {
            $response->assertSee($domain->domain);
        });

        // Other member on team
        $response = $this->actingAs($otherTeamMember)
            ->get(route('projects.environments.domains', [$project, $environment]))
            ->assertSuccessful();

        $domains->each(function ($domain) use ($response) {
            $response->assertSee($domain->domain);
        });

        // User NOT on team
        $response = $this->actingAs($this->other)
            ->get(route('projects.environments.domains', [$project, $environment]))
            ->assertForbidden();

        $domains->each(function ($domain) use ($response) {
            $response->assertDontSee($domain->domain);
        });
    }
}

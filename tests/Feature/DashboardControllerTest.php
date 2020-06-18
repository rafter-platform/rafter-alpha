<?php

namespace Tests\Feature;

use App\Environment;
use App\Events\UserRegistered;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        event(new UserRegistered($this->user));
    }

    public function test_redirects_to_project_create_if_no_projects()
    {
        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertRedirect('/projects/create');
    }

    public function test_see_projects_on_dashboard()
    {
        $project = factory(Project::class)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        factory(Environment::class)->create([
            'project_id' => $project->id,
        ]);

        $this->user->refresh();

        $this->actingAs($this->user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee($project->name);
    }
}

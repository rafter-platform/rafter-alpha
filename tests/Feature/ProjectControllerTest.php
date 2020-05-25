<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Project;
use App\User;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherProject;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->otherProject = factory(Project::class)->create();
    }

    public function test_user_can_view_their_projects()
    {
        $projects = factory(Project::class, 3)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get('/projects')
            ->assertSuccessful();

        $projects->each(function ($project) use ($response) {
            $response->assertSee(e($project->name));
        });

        $response->assertDontSee($this->otherProject->name);
    }
}

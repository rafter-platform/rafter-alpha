<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherProject;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->otherProject = factory('App\Project')->create();
    }

    public function test_user_can_view_their_projects()
    {
        $projects = factory('App\Project', 3)->create([
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

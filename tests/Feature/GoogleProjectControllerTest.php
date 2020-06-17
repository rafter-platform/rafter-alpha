<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $otherProject;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->otherProject = factory('App\GoogleProject')->create();
    }

    public function test_user_can_view_their_projects()
    {
        $projects = factory('App\GoogleProject', 3)->create([
            'team_id' => $this->user->currentTeam->id,
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get('/google-projects')
            ->assertSuccessful();

        $projects->each(function ($project) use ($response) {
            $response->assertSee(e($project->project_id));
        });

        $response->assertDontSee($this->otherProject->project_id);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DatabaseInstanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->other = factory('App\DatabaseInstance')->create();
    }

    public function test_user_can_view_their_database_instances()
    {
        $dbs = factory('App\DatabaseInstance', 3)->create([
            'google_project_id' => factory('App\GoogleProject')->create([
                'team_id' => $this->user->currentTeam->id,
            ]),
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get('/database-instances')
            ->assertSuccessful();

        $dbs->each(function ($db) use ($response) {
            $response->assertSee(e($db->name));
        });

        $response->assertDontSee($this->other->name);

        $this->actingAs($this->user)
            ->get('/database-instances/'.$dbs->first()->id)
            ->assertSuccessful();

        $this->actingAs($this->user)
            ->get('/database-instances/'.$this->other->id)
            ->assertForbidden();
    }
}

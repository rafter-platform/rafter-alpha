<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\GoogleProject;
use App\DatabaseInstance;
use App\User;

class DatabaseInstanceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->other = factory(DatabaseInstance::class)->create();
    }

    public function test_user_can_view_their_database_instances()
    {
        $dbs = factory(DatabaseInstance::class, 3)->create([
            'google_project_id' => factory(GoogleProject::class)->create([
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

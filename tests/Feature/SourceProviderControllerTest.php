<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SourceProviderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $other;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory('App\User')->create();
        $this->other = factory('App\SourceProvider')->create();
    }

    public function test_user_can_view_their_source_providers()
    {
        $sources = factory('App\SourceProvider', 3)->create([
            'user_id' => $this->user->id,
        ]);

        $this->user->currentTeam->refresh();

        $response = $this->actingAs($this->user)
            ->get('/source-providers')
            ->assertSuccessful();

        $sources->each(function ($source) use ($response) {
            $response->assertSee(e($source->name));
        });

        $response->assertDontSee($this->other->name);

        $response = $this->actingAs($this->user)
            ->get('/source-providers/' . $sources->first()->id . '/edit')
            ->assertSuccessful();

        $response = $this->actingAs($this->user)
            ->get('/source-providers/' . $this->other->id . '/edit')
            ->assertForbidden();
    }
}

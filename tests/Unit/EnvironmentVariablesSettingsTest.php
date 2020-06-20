<?php

namespace Tests\Unit;

use App\Environment;
use App\Events\UserRegistered;
use App\Http\Livewire\EnvironmentVariablesSettings;
use App\Project;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnvironmentVariablesSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $environment;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        UserRegistered::dispatch($this->user);

        $this->environment = factory(Environment::class)->create([
            'project_id' => factory(Project::class)->create([
                'team_id' => $this->user->currentTeam->id,
            ]),
        ]);
    }

    public function test_variables_are_saved()
    {
        $this->assertEquals('', $this->environment->environmental_variables);

        Livewire::actingAs($this->user)
            ->test(EnvironmentVariablesSettings::class, ['environment' => $this->environment])
            ->set('variables', "SOME='thing'")
            ->call('handle');

        $this->assertEquals("SOME='thing'", $this->environment->refresh()->environmental_variables);
    }
}

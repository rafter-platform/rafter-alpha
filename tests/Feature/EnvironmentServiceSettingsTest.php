<?php

namespace Tests\Feature;

use App\Environment;
use App\Http\Livewire\EnvironmentServiceSettings;
use App\Project;
use App\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EnvironmentServiceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $environment;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        event(new Registered($this->user));

        $this->environment = factory(Environment::class)->create([
            'project_id' => factory(Project::class)->create([
                'team_id' => $this->user->currentTeam->id,
            ]),
        ]);
    }

    public function test_it_validates_integer_sizes()
    {
        $this->actingAs($this->user);

        Livewire::test(EnvironmentServiceSettings::class, ['environment' => $this->environment, 'type' => 'web'])
            ->set('memory', '1Gi')
            ->set('cpu', 1)
            ->set('requestTimeout', 1000)
            ->set('maxRequestsPerContainer', 1000)
            ->set('maxInstances', 2000)
            ->assertHasErrors([
                'requestTimeout' => 'between',
                'maxRequestsPerContainer' => 'between',
                'maxInstances' => 'between',
            ]);
    }

    public function test_it_sets_new_values()
    {
        $this->actingAs($this->user);

        Livewire::test(EnvironmentServiceSettings::class, ['environment' => $this->environment, 'type' => 'web'])
            ->set('memory', '2Gi')
            ->set('cpu', 2)
            ->set('requestTimeout', 400)
            ->set('maxRequestsPerContainer', 50)
            ->set('maxInstances', 900)
            ->call('handle')
            ->assertDispatchedBrowserEvent('notify');

        $this->environment->refresh();

        $this->assertSame('2Gi', $this->environment->getOption('web_memory'));
        $this->assertSame(2, $this->environment->getOption('web_cpu'));
        $this->assertSame(400, $this->environment->getOption('web_request_timeout'));
        $this->assertSame(50, $this->environment->getOption('web_max_requests_per_container'));
        $this->assertSame(900, $this->environment->getOption('web_max_instances'));
    }

    public function test_it_authorizes_the_user()
    {
        $this->actingAs(factory(User::class)->create());

        Livewire::test(EnvironmentServiceSettings::class, ['environment' => $this->environment, 'type' => 'web'])
            ->call('handle')
            ->assertForbidden();
    }
}

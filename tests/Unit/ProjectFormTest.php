<?php

namespace Tests\Unit;

use App\Http\Livewire\ProjectForm;
use App\SourceProvider;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_required_fields()
    {
        $this->actingAs(factory(User::class)->create());

        Livewire::test(ProjectForm::class)
            ->call('create')
            ->assertHasErrors([
                'repository' => 'required',
                'name' => 'required',
                'googleProjectId' => 'required',
                'region' => 'required',
                'type' => 'required',
            ]);
    }
}

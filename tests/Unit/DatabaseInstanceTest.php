<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseInstanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_connection_string_is_generated()
    {
        $instance = factory('App\DatabaseInstance')->create([
            'google_project_id' => factory('App\GoogleProject')->create([
                'project_id' => 'some-project',
            ])->id,
            'name' => 'my-db',
        ]);

        $this->assertSame('some-project:us-central1:my-db', $instance->connectionString());
    }

    public function test_some_options_are_accessible_as_properties()
    {
        $instance = factory('App\DatabaseInstance')->create();

        $this->assertSame('MYSQL_5_7', $instance->version);
        $this->assertSame('db-f1-micro', $instance->tier);
        $this->assertSame('10', $instance->size);
        $this->assertSame('us-central1', $instance->region);
        $this->assertSame('notapassword', $instance->rootPassword);
    }
}

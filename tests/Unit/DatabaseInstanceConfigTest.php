<?php

namespace Tests\Unit;

use App\GoogleCloud\DatabaseInstanceConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseInstanceConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_expected_defaults_are_set()
    {
        $instance = factory('App\DatabaseInstance')->make();

        $config = (new DatabaseInstanceConfig($instance))->config();

        $this->assertSame('MYSQL_5_7', $config['databaseVersion']);
        $this->assertEquals([
            'tier' => 'db-f1-micro',
            'kind' => 'sql#settings',
            'dataDiskSizeGb' => 10,
            'backupConfiguration' => [
                'enabled' => true,
                'kind' => 'sql#backupConfiguration',
                'binaryLogEnabled' => true,
            ],
        ], $config['settings']);
        $this->assertSame($instance->name, $config['name']);
        $this->assertSame('us-central1', $config['region']);
        $this->assertSame('notapassword', $config['rootPassword']);
    }
}

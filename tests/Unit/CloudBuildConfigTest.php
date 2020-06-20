<?php

namespace Tests\Unit;

use App\Deployment;
use App\GoogleCloud\CloudBuildConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CloudBuildConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_has_basic_items()
    {
        $deployment = factory(Deployment::class)->create();

        $config = new CloudBuildConfig($deployment);

        $this->assertArrayHasKey('steps', $config->instructions());
        $this->assertArrayHasKey('images', $config->instructions());
    }

    public function test_has_basic_steps()
    {
        $deployment = factory(Deployment::class)->create();

        $config = new CloudBuildConfig($deployment);

        $steps = $config->instructions()['steps'];

        // Starts by pulling cache
        $this->assertStringStartsWith('docker pull', $steps->first()['args'][1]);

        // It should pull the git repo
        $step = $steps->firstWhere('name', 'gcr.io/cloud-builders/curl');
        $this->assertStringStartsWith('curl -L --output repo.tar.gz', $step['args'][1]);
    }
}

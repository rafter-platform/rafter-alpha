<?php

namespace Tests;

use App\Services\GitHub;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mockery\MockInterface;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Load a stub
     *
     * @param string $name
     * @return array
     */
    protected function loadStub($name)
    {
        return json_decode(file_get_contents(__DIR__ . "/stubs/{$name}.json"), true);
    }

    protected function mockGitHubForDeployment(): MockInterface
    {
        $mock = GitHub::mock();
        $mock->shouldReceive('token')->andReturn('notatoken');
        $mock->shouldReceive('tarballUrl')->andReturn('https://something.tar.gz');
        $mock->shouldReceive('latestHashFor')->andReturn('abc123');
        $mock->shouldReceive('createDeployment')->andReturn(['id' => 123]);
        $mock->shouldReceive('updateDeploymentStatus');
        $mock->shouldReceive('validRepository')->andReturn(true);
        $mock->shouldReceive('getRepositories')->andReturn(['repositories' => []]);

        return $mock;
    }
}

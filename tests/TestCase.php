<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

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
}

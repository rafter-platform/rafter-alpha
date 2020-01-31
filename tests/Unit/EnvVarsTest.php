<?php

namespace Tests\Unit;

use App\EnvVars;
use Tests\TestCase;

class EnvVarsTest extends TestCase
{
    public function test_it_extracts_vars_from_string()
    {
        $string = <<<EOD
FOO=bar
LONG="This
Is a
Long Name"
EOD;

        $vars = EnvVars::fromString($string);

        $this->assertEquals([
            [
                'name' => 'FOO',
                'value' => 'bar',
            ],
            [
                'name' => 'LONG',
                'value' => "This\nIs a\nLong Name",
            ],
        ], $vars->all());
    }

    public function test_it_sets_and_gets_variables()
    {
        $vars = new EnvVars;

        $vars->set('HELLO', 'world');

        $this->assertEquals('world', $vars->get('HELLO'));
    }

    public function test_it_populates_vars_with_constructor()
    {
        $vars = new EnvVars([
            'HI' => 'there',
        ]);

        $this->assertEquals('there', $vars->get('HI'));
    }

    public function test_it_gets_internal_object_with_get_no_key()
    {
        $vars = new EnvVars([
            'HI' => 'gang',
        ]);

        $this->assertEquals([
            'HI' => 'gang'
        ], $vars->get());
    }

    public function test_it_dumps_vars_to_string()
    {
        $vars = new EnvVars([
            'HELLO' => 'world',
            'IT' => 'me',
        ]);

        $string = <<<EOD
HELLO=world
IT=me
EOD;
        $this->assertEquals($string, $vars->toString());
        $this->assertEquals($string, (string) $vars);
    }

    public function test_it_injects_vars()
    {
        $vars = new EnvVars([
            'NAME' => 'Josh',
        ]);

        $vars->inject([
            'JOB' => 'Developer',
            'FOOD' => 'Pizza',
        ]);

        $this->assertEquals([
            'NAME' => 'Josh',
            'JOB' => 'Developer',
            'FOOD' => 'Pizza',
        ], $vars->get());
    }
}

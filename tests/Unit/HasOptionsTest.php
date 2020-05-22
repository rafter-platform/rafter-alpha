<?php

namespace Tests\Unit;

use App\Casts\Options;
use App\HasOptions;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Tests\TestCase;

class HasOptionsTest extends TestCase
{
    public function test_default_options_are_applied()
    {
        $model = new TestModel([
            'options' => [],
        ]);

        $this->assertEquals('100GB', $model->getOption('size'));
        $this->assertEquals(true, $model->getOption('backups.enabled'));
    }

    public function test_options_are_merged()
    {
        $model = new TestModel([
            'options' => [
                'size' => '200GB',
                'backups' => [
                    'location' => 'us-central1',
                ],
            ],
        ]);

        $this->assertEquals('200GB', $model->getOption('size'));
        $this->assertEquals(true, $model->getOption('backups.enabled'));
        $this->assertEquals('us-central1', $model->getOption('backups.location'));
    }

    public function test_options_are_set()
    {
        $model = new TestModel([
            'options' => [],
        ]);

        // Directly set on the options object, because otherwise $this->save() is
        // called on the setOption helper method, and this is just a dummy model.
        $model->options['size'] = '150GB';
        $model->options['backups.location'] = 'us-east';

        $this->assertEquals('150GB', $model->getOption('size'));
        $this->assertEquals(true, $model->getOption('backups.enabled'));
        $this->assertEquals('us-east', $model->getOption('backups.location'));

        $this->assertEquals([
            'size' => '150GB',
            'backups' => [
                'location' => 'us-east',
            ],
        ], $model->options->toArray());

        $model->options = [
            'something' => 'fun',
        ];

        $this->assertEquals('fun', $model->getOption('something'));

        $this->assertEquals([
            'something' => 'fun',
        ], $model->options->toArray());
    }

    public function test_options_arrays_are_merged()
    {
        $model = new TestModel([
            'options' => [],
        ]);

        $model->options['access.public'] = true;
        $model->options['size'] = '150GB';
        $model->options['backups.location'] = 'us-east';

        $this->assertEquals(true, $model->getOption('access.public'));

        $this->assertEquals([
            'location' => 'us-east',
            'enabled' => true,
            'frequency' => 'daily',
        ], $model->getOption('backups'));

        $this->assertEquals([
            'public' => true,
        ], $model->getOption('access'));
    }
}

class TestModel extends EloquentModel
{
    use HasOptions;

    protected $guarded = [];

    protected $casts = [
        'options' => Options::class,
    ];

    protected $defaultOptions = [
        'size' => '100GB',
        'backups' => [
            'enabled' => true,
            'frequency' => 'daily',
        ],
    ];
}

<?php

use App\Project;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Environment;
use Faker\Generator as Faker;

$factory->define(Environment::class, function (Faker $faker) {
    return [
        'project_id' => factory(Project::class),
        'name' => $faker->slug(),
    ];
});

$factory->state(Environment::class, 'laravel', function ($faker) {
    return [
        'project_id' => factory(Project::class)->state('laravel'),
    ];
});

$factory->state(Environment::class, 'nodejs', function ($faker) {
    return [
        'project_id' => factory(Project::class)->state('nodejs'),
    ];
});

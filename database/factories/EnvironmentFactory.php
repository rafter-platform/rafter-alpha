<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Environment;
use Faker\Generator as Faker;

$factory->define(Environment::class, function (Faker $faker) {
    return [
        'project_id' => factory('App\Project'),
        'name' => $faker->slug(),
    ];
});

$factory->state(Environment::class, 'laravel', function ($faker) {
    return [
        'project_id' => factory('App\Project')->state('laravel'),
    ];
});

$factory->state(Environment::class, 'nodejs', function ($faker) {
    return [
        'project_id' => factory('App\Project')->state('nodejs'),
    ];
});

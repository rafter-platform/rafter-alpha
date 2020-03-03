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

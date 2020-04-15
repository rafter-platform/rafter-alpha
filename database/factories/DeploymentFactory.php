<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Deployment;
use Faker\Generator as Faker;

$factory->define(Deployment::class, function (Faker $faker) {
    return [
        'environment_id' => factory('App\Environment'),
        'initiator_id' => factory('App\User'),
        'commit_message' => $faker->text(40),
    ];
});

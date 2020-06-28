<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Command;
use Faker\Generator as Faker;

$factory->define(Command::class, function (Faker $faker) {
    return [
        'command' => $faker->text(12) . ' ' . rand(),
        'user_id' => factory('App\User'),
        'environment_id' => factory('App\Environment'),
        'status' => 'pending',
    ];
});

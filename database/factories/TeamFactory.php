<?php

use App\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Team;
use Faker\Generator as Faker;

$factory->define(Team::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'name' => $faker->name(),
    ];
});

<?php

use App\User;
use App\Environment;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Command;
use Faker\Generator as Faker;

$factory->define(Command::class, function (Faker $faker) {
    return [
        'command' => $faker->text(12),
        'user_id' => factory(User::class),
        'environment_id' => factory(Environment::class),
        'status' => 'pending',
    ];
});

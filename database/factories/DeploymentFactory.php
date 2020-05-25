<?php

use App\Environment;
use App\User;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Deployment;
use Faker\Generator as Faker;

$factory->define(Deployment::class, function (Faker $faker) {
    return [
        'environment_id' => factory(Environment::class),
        'initiator_id' => factory(User::class),
        'commit_message' => $faker->text(40),
    ];
});

<?php

use App\DatabaseInstance;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Database;
use Faker\Generator as Faker;

$factory->define(Database::class, function (Faker $faker) {
    return [
        'name' => $faker->slug(),
        'database_instance_id' => factory(DatabaseInstance::class),
    ];
});

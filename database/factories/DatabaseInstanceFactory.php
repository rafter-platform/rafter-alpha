<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DatabaseInstance;
use Faker\Generator as Faker;

$factory->define(DatabaseInstance::class, function (Faker $faker) {
    return [
        'google_project_id' => factory('App\GoogleProject'),
        'name' => $faker->slug,
        'type' => 'mysql',
        'options' => [
            'version' => 'MYSQL_5_7',
            'tier' => 'db-f1-micro',
            'size' => '10',
            'region' => 'us-central1',
            'rootPassword' => 'notapassword',
        ],
    ];
});

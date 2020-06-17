<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\GoogleProject;
use Faker\Generator as Faker;

$factory->define(GoogleProject::class, function (Faker $faker) {
    return [
        'team_id' => factory('App\Team'),
        'project_id' => $faker->slug(),
        'service_account_json' => [
            'some' => 'secrets',
            'client_email' => 'rafter@rafter.service.account.com',
        ],
    ];
});

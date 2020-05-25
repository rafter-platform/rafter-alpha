<?php

use App\Team;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\GoogleProject;
use Faker\Generator as Faker;

$factory->define(GoogleProject::class, function (Faker $faker) {
    return [
        'team_id' => factory(Team::class),
        'project_id' => $faker->slug(),
        'name' => $faker->text(20),
        'service_account_json' => [
            'some' => 'secrets',
            'client_email' => 'rafter@rafter.service.account.com',
        ],
    ];
});

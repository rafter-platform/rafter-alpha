<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Project;
use Faker\Generator as Faker;

$factory->define(Project::class, function (Faker $faker) {
    return [
        'name' => $faker->name(),
        'type' => $faker->randomElement(Project::TYPES),
        'team_id' => factory('App\Team'),
        'google_project_id' => factory('App\GoogleProject'),
        'source_provider_id' => factory('App\SourceProvider'),
        'region' => 'us-central1',
    ];
});

$factory->state(Project::class, 'laravel', [
    'type' => 'laravel',
]);

$factory->state(Project::class, 'nodejs', [
    'type' => 'nodejs',
]);

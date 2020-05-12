<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\DomainMapping;
use Faker\Generator as Faker;

$factory->define(DomainMapping::class, function (Faker $faker) {
    return [
        'domain' => $faker->domainName,
        'status' => 'inactive',
        'environment_id' => factory('App\Environment'),
    ];
});

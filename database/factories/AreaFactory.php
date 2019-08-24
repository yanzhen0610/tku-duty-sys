<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Area;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Area::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid()->toString(),
        'area_name' => $faker->city,
        'responsible_person_id' => App\User::inRandomOrder()->first(),
    ];
});

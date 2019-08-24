<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Shift;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Shift::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid()->toString(),
        'area_id' => App\Area::inRandomOrder()->first(),
        'shift_name' => $faker->streetName,
    ];
});

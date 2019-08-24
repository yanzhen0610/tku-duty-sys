<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\ShiftArrangement;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(ShiftArrangement::class, function (Faker $faker) {
    return [
        'uuid' => Str::uuid()->toString(),
        'shift_id' => App\Shift::inRandomOrder()->first(),
        'on_duty_staff_id' => App\User::inRandomOrder()->first(),
        'date' => $faker->dateTimeBetween($startDate = '-30 days', $endDate = '30 days')->format('Y-m-d'),
    ];
});

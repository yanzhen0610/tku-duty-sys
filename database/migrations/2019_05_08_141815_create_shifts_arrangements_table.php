<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftsArrangementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts_arrangements', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->uuid('uuid')->unique()->index();

            $table->bigInteger('shift_id')->unsigned()->index();
            $table->foreign('shift_id')->references('id')->on('shifts')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->bigInteger('on_duty_staff_id')->unsigned()->index();
            $table->foreign('on_duty_staff_id')->references('id')
                ->on('users')->onDelete('restrict')->onUpdate('cascade');

            $table->date('date')->index();

            $table->timestamps();

            $table->unique(['shift_id', 'on_duty_staff_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shifts_arrangements');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->uuid('uuid')->unique()->index();

            $table->bigInteger('area_id')->unsigned();
            $table->foreign('area_id')->references('id')->on('areas')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->string('shift_name');

            $table->text('working_time')->nullable();
            $table->text('working_hours')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shifts');
    }
}

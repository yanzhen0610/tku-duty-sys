<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShiftArrangementChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_arrangement_changes', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->uuid('uuid')->unique()->index();

            $table->bigInteger('shift_id')->unsigned()->index();
            $table->date('date')->index();
            $table->bigInteger('on_duty_staff_id')->unsigned()->index();
            $table->bigInteger('changer_id')->unsigned()->index();
            $table->boolean('is_locked')->index();
            $table->boolean('is_up')->index();

            $table->foreign('shift_id')->references('id')
                ->on('shifts')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('on_duty_staff_id')->references('id')
                ->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('changer_id')->references('id')
                ->on('users')->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('shift_arrangement_changes');
    }
}

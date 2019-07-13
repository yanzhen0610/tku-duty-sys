<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Reference;

class CreateShiftArrangementLocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_arrangement_locks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->bigInteger('shift_id')->unsigned()->index();
            $table->date('date')->index();
            $table->boolean('is_locked')->index();

            $table->foreign('shift_id')->references('id')
                ->on('shifts')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();

            $table->unique(['shift_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shift_arrangement_locks');
    }
}

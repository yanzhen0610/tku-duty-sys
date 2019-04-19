<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->bigIncrements('id')->index();

            $table->unsignedInteger('level')->index();
            $table->foreign('level')->references('id')->on('log_levels')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedInteger('tag')->index();
            $table->foreign('tag')->references('id')->on('log_tags')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->text('message');

            $table->timestamps();
            $table->index(['created_at', 'updated_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}

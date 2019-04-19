<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersActionTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_action_tokens', function (Blueprint $table) {
            $table->bigIncrements('id')->index();

            $table->unsignedInteger('action')->index();
            $table->foreign('action')->references('id')
                ->on('users_action_tokens_action_types')
                ->onDelete('restrict')->onUpdate('cascade');

            $table->string('token')->unique()->index();

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
        Schema::dropIfExists('users_action_tokens');
    }
}

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_levels', function (Blueprint $table) {
            $table->bigIncrements('id')->index();

            $table->string('level_name')->index();
            $table->integer('level')->index();
        });

        DB::table('log_levels')->insert([
            'level_name' => 'Info',
            'level' => 1,
        ]);

        DB::table('log_levels')->insert([
            'level_name' => 'Important',
            'level' => 2,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('log_levels');
    }
}

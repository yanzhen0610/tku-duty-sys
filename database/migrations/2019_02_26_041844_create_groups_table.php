<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->uuid('uuid')->unique()->index();
            $table->string('group_name')->index();
            $table->timestamps();
        });

        DB::table('groups')->insert([
            'group_name' => 'admin',
            'uuid' => Str::uuid()->toString(),
        ]);

        DB::table('groups')->insert([
            'group_name' => 'disabled',
            'uuid' => Str::uuid()->toString(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('groups');
    }
}

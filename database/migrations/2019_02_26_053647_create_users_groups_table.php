<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_groups', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unsignedInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            
            $table->timestamps();
            
            $table->unique(['user_id', 'group_id']);
            $table->index(['id', 'user_id', 'group_id']);
        });

        DB::table('users_groups')->insert([
            'user_id' => DB::table('users')->select('id')->where('username', 'admin')->first()->id,
            'group_id' => DB::table('groups')->select('id')->where('group_name', 'admin')->first()->id,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_groups');
    }
}

<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->string('username')->index();
            $table->string('display_name')->nullable();
            $table->string('password');
            $table->integer('status')->default(0);
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile_ext')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['username', 'deleted_at']);
            $table->unique(['email', 'deleted_at']);
        });

        DB::table('users')->insert([
            'username' => env('ADMIN_USERNAME', 'admin'),
            'display_name' => env('ADMIN_DISPLAY_NAME', __('ui.admin')),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'admin')),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}

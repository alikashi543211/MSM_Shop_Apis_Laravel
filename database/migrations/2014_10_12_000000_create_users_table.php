<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_no');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->text('verification_token')->nullable();
            $table->boolean('is_otp_verified')->default(false);
            $table->string('otp')->nullable();
            $table->string('password')->nullable();
            $table->bigInteger('role_id')->unsigned();
            $table->bigInteger('created_by')->unsigned()->nullable();
            $table->integer('status')->unsigned()->default(ACTIVE);
            $table->string('profile_picture')->default('images/user-placeholder.png');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
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

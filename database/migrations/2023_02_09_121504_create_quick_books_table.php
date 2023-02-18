<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuickBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quick_books', function (Blueprint $table) {
            $table->id();
            $table->text('access_token_key');
            $table->string('token_type')->default('bearer');
            $table->text('refresh_token');
            $table->string('access_token_expires_at');
            $table->string('refresh_token_expires_at');
            $table->string('access_token_validation_period');
            $table->string('refresh_token_validation_period');
            $table->string('client_id');
            $table->string('client_secret');
            $table->string('real_mid');
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
        Schema::dropIfExists('quick_books');
    }
}

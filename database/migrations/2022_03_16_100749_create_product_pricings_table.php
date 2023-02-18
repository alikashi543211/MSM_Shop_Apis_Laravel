<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPricingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('retail_cost')->nullable();
            $table->string('shipping')->nullable();
            $table->string('tariff_code')->nullable();
            $table->string('cpc')->nullable();
            $table->string('duty')->nullable();
            $table->string('wharfage')->nullable();
            $table->string('insurance')->nullable();
            $table->string('fuel_adjustment')->nullable();
            $table->string('landed_cost')->nullable();
            $table->bigInteger('product_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('product_pricings', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_pricings');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_merchants', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('merchant_id')->unsigned();
            $table->bigInteger('product_id')->unsigned();
            $table->integer('sort_number')->nullable();
            $table->string('sku');
            $table->string('link')->nullable();
            $table->float('retail_cost');
            $table->float('import_taxes');
            $table->float('duty');
            $table->float('wharfage');
            $table->float('shipping_charges');
            $table->float('shipping');
            $table->float('fuel_adjustment');
            $table->float('insurance');
            $table->float('estimated_landed_cost');
            $table->timestamps();
        });

        Schema::table('product_merchants', function (Blueprint $table) {
            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('cascade');
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
        Schema::dropIfExists('product_merchants');
    }
}

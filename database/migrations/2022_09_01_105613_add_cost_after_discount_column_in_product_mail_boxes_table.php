<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostAfterDiscountColumnInProductMailBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_mail_boxes', 'cost_after_discount')) {

            Schema::table('product_mail_boxes', function (Blueprint $table) {
                $table->float('cost_after_discount')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('product_mail_boxes', 'discount_amount')) {

            Schema::table('product_mail_boxes', function (Blueprint $table) {
                $table->float('discount_amount')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_mail_boxes', function (Blueprint $table) {
            //
        });
    }
}

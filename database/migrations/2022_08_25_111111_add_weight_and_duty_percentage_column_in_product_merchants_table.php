<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWeightAndDutyPercentageColumnInProductMerchantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_merchants', 'discount')) {

            Schema::table('product_merchants', function (Blueprint $table) {
                $table->float('weight')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('product_merchants', 'discount_type')) {

            Schema::table('product_merchants', function (Blueprint $table) {
                $table->float('duty_percentage')->nullable()->after('id');
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
        Schema::table('product_merchants', function (Blueprint $table) {
            //
        });
    }
}

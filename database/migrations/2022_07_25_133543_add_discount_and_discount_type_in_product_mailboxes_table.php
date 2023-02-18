<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDiscountAndDiscountTypeInProductMailboxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_mail_boxes', 'discount')) {

            Schema::table('product_mail_boxes', function (Blueprint $table) {
                $table->float('discount')->nullable()->after('id');
            });
        }
        if (!Schema::hasColumn('product_mail_boxes', 'discount_type')) {

            Schema::table('product_mail_boxes', function (Blueprint $table) {
                $table->string('discount_type')->default(FLAT_DISCOUNT_TYPE)->after('id');
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
            $table->dropColumn('discount');
            $table->dropColumn('discount_type');
        });
    }
}

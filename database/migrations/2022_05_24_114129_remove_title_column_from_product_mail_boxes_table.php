<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveTitleColumnFromProductMailBoxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('product_mail_boxes', 'title')) {

            Schema::table('product_mail_boxes', function (Blueprint $table) {
                $table->dropColumn('title');
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

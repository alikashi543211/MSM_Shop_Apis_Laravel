<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLastUpdatedAndReservedTimeColumnInCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('carts', 'is_last_updated')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->boolean('is_last_updated')->default(0)->after('id');
            });
        }
        if (!Schema::hasColumn('carts', 'reserved_time')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('reserved_time')->nullable()->after('id');
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
        Schema::table('carts', function (Blueprint $table) {
            //
        });
    }
}

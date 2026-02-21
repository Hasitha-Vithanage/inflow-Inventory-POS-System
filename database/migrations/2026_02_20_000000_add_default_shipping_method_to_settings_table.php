<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultShippingMethodToSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            // Check if column exists first to be safe
            if (!Schema::hasColumn('settings', 'default_shipping_method_id')) {
                $table->unsignedBigInteger('default_shipping_method_id')->nullable()->after('default_tax');
                $table->foreign('default_shipping_method_id')->references('id')->on('shipping_methods')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'default_shipping_method_id')) {
                $table->dropForeign(['default_shipping_method_id']);
                $table->dropColumn('default_shipping_method_id');
            }
        });
    }
}

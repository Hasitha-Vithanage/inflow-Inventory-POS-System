<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveCompanyIdFromShippingMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            // 1. Drop Foreign Key
            $table->dropForeign(['shipping_company_id']);
            
            // 2. Drop Column
            $table->dropColumn('shipping_company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('shipping_company_id')->nullable()->after('name');
            $table->foreign('shipping_company_id')->references('id')->on('shipping_companies')->onDelete('set null');
        });
    }
}

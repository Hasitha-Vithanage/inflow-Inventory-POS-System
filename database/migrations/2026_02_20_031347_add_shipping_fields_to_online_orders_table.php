<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('online_orders', 'shipping_method_id')) {
                $table->integer('shipping_method_id')->nullable()->after('warehouse_id');
            }
            if (!Schema::hasColumn('online_orders', 'shipping_company_id')) {
                $table->integer('shipping_company_id')->nullable()->after('shipping_method_id');
            }
            if (!Schema::hasColumn('online_orders', 'shipping_status')) {
                $table->string('shipping_status', 20)->default('ordered')->after('shipping_company_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_method_id', 'shipping_company_id', 'shipping_status']);
        });
    }
};

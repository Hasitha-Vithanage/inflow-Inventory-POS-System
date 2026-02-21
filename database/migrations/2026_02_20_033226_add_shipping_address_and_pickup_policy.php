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
        if (Schema::hasTable('online_orders')) {
            Schema::table('online_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('online_orders', 'shipping_name')) {
                    $table->string('shipping_name')->nullable()->after('shipping_status');
                    $table->string('shipping_phone')->nullable()->after('shipping_name');
                    $table->string('shipping_address')->nullable()->after('shipping_phone');
                    $table->string('shipping_city')->nullable()->after('shipping_address');
                    $table->string('shipping_country')->nullable()->after('shipping_city');
                }
            });
        }

        if (Schema::hasTable('store_settings')) {
            Schema::table('store_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('store_settings', 'pickup_policy')) {
                    $table->text('pickup_policy')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('online_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_name', 'shipping_phone', 'shipping_address', 'shipping_city', 'shipping_country']);
        });

        Schema::table('store_settings', function (Blueprint $table) {
            $table->dropColumn('pickup_policy');
        });
    }
};

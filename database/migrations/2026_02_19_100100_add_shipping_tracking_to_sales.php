<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'shipping_method_id')) {
                $table->unsignedBigInteger('shipping_method_id')->nullable()->after('shipping_status');
                $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('set null');
            }

            if (!Schema::hasColumn('sales', 'shipping_company_id')) {
                $table->unsignedBigInteger('shipping_company_id')->nullable()->after('shipping_method_id');
                $table->foreign('shipping_company_id')->references('id')->on('shipping_companies')->onDelete('set null');
            }

            if (!Schema::hasColumn('sales', 'tracking_number')) {
                $table->string('tracking_number')->nullable()->after('shipping_company_id');
            }

            if (!Schema::hasColumn('sales', 'packed_at')) {
                $table->timestamp('packed_at')->nullable()->after('tracking_number');
            }

            if (!Schema::hasColumn('sales', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('packed_at');
            }

            if (!Schema::hasColumn('sales', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            }
        });

        // Ensure Ref has a unique index (use try-catch in case it already exists)
        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->unique('Ref');
            });
        } catch (\Exception $e) {
            // Index already exists, skip
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop FKs first
            if (Schema::hasColumn('sales', 'shipping_method_id')) {
                $table->dropForeign(['shipping_method_id']);
            }
            if (Schema::hasColumn('sales', 'shipping_company_id')) {
                $table->dropForeign(['shipping_company_id']);
            }

            $columns = [];
            foreach (['shipping_method_id', 'shipping_company_id', 'tracking_number', 'packed_at', 'shipped_at', 'delivered_at'] as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $columns[] = $col;
                }
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};

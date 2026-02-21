<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            if (!Schema::hasColumn('shipping_methods', 'shipping_company_id')) {
                $table->unsignedBigInteger('shipping_company_id')->nullable()->after('name');
                $table->foreign('shipping_company_id')->references('id')->on('shipping_companies')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shipping_methods', function (Blueprint $table) {
            $table->dropForeign(['shipping_company_id']);
            $table->dropColumn('shipping_company_id');
        });
    }
};

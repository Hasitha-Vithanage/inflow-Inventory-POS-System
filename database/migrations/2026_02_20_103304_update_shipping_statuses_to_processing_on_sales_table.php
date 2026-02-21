<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all 'ordered' shipping statuses to 'processing'
        DB::table('sales')->where('shipping_status', 'ordered')->update(['shipping_status' => 'processing']);
        DB::table('shipments')->where('status', 'ordered')->update(['status' => 'processing']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'processing' back to 'ordered'
        DB::table('sales')->where('shipping_status', 'processing')->update(['shipping_status' => 'ordered']);
        DB::table('shipments')->where('status', 'processing')->update(['status' => 'ordered']);
    }
};

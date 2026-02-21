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
        // Update any remaining 'ordered' or 'shipped' statuses
        DB::table('sales')
            ->where('shipping_status', 'ordered')
            ->update(['shipping_status' => 'processing']);
            
        DB::table('sales')
            ->where('shipping_status', 'shipped')
            ->update(['shipping_status' => 'dispatched']);

        DB::table('shipments')
            ->where('status', 'ordered')
            ->update(['status' => 'processing']);
            
        DB::table('shipments')
            ->where('status', 'shipped')
            ->update(['status' => 'dispatched']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('sales')
            ->where('shipping_status', 'dispatched')
            ->update(['shipping_status' => 'shipped']);

        DB::table('shipments')
            ->where('status', 'dispatched')
            ->update(['status' => 'shipped']);
    }
};

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
        if (!Schema::hasTable('notification_logs')) {
            Schema::create('notification_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->string('channel', 50); // email, sms, whatsapp
                $table->string('status', 50); // sent, failed
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};

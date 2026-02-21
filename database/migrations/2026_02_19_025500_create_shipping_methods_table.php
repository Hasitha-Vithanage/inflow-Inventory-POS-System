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
        if (!Schema::hasTable('shipping_methods')) {
            Schema::create('shipping_methods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('shipping_company_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('shipping_company_id')->references('id')->on('shipping_companies')->onDelete('set null');
            });
        } else {
            Schema::table('shipping_methods', function (Blueprint $table) {
                if (!Schema::hasColumn('shipping_methods', 'name')) {
                    $table->string('name');
                }
                if (!Schema::hasColumn('shipping_methods', 'shipping_company_id')) {
                    $table->unsignedBigInteger('shipping_company_id')->nullable();
                    // Foreign key might exist or not, hard to check easily, skipping for simplicity or assuming standard.
                    // If we really need to check FK, it's more complex. Ideally we assume if column exists, FK might too.
                    // If not, we could try adding it in a try-catch block or just rely on column.
                    // Let's add the FK constraint if we add the column.
                    $table->foreign('shipping_company_id')->references('id')->on('shipping_companies')->onDelete('set null');
                }
                if (!Schema::hasColumn('shipping_methods', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                }
                if (!Schema::hasColumn('shipping_methods', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};

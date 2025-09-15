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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->string('sku', 100);
            $table->string('location', 100)->default('default');
            $table->string('type', 50);
            $table->integer('quantity');
            $table->string('reason', 255)->nullable();
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_id', 100)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at', 6);

            $table->primary(['tenant_id', 'id']);
            $table->index(['sku', 'location', 'created_at']);
            $table->index('type');
            $table->index(['reference_type', 'reference_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign(['tenant_id', 'sku'])->references(['tenant_id', 'sku'])->on('product_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};

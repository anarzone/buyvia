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
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->string('sku', 100);
            $table->ulid('order_id', 16);
            $table->integer('quantity');
            $table->timestamp('expires_at', 6);
            $table->timestamp('created_at', 6);

            $table->primary(['tenant_id', 'id']);

            $table->unique(['tenant_id', 'sku', 'order_id']);
            $table->index('tenant_id','expires_at');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};

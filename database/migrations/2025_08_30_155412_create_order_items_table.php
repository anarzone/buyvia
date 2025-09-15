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
        Schema::create('order_items', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->ulid('order_id', 16);
            $table->string('product_name', 255);
            $table->string('product_sku', 100);
            $table->ulid('product_variant_id', 16)->nullable();
            $table->integer('quantity');
            $table->bigInteger('unit_price_cents');
            $table->decimal('tax_rate', 5)->default(0);
            $table->bigInteger('total_cents');
            $table->json('product_snapshot')->nullable();
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();

            $table->primary(['tenant_id', 'id']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign(['tenant_id', 'order_id'])->references(['tenant_id', 'id'])->on('orders')->onDelete('restrict');

            // Note: Product variant relationship handled at application level
            // Order items preserve product data even when variants are deleted

            $table->index('order_id');
            $table->index('product_sku');
            $table->index(['tenant_id', 'order_id'], 'idx_items_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

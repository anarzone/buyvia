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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->ulid('product_id', 16);
            $table->string('sku', 64);
            $table->bigInteger('price_cents');
            $table->char('currency', 3);
            $table->json('attr')->nullable();
            $table->integer('weight_g')->nullable();
            $table->string('barcode', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();

            // Generated columns for JSON attributes
            $table->string('color', 32)->nullable()->storedAs("JSON_UNQUOTE(JSON_EXTRACT(attr,'$.color'))");
            $table->string('size', 32)->nullable()->storedAs("JSON_UNQUOTE(JSON_EXTRACT(attr,'$.size'))");

            // Primary key and constraints
            $table->primary(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'sku'], 'uniq_variants_sku_per_tenant');
            $table->index(['tenant_id', 'product_id'], 'idx_variants_product');
            $table->index(['tenant_id', 'color'], 'idx_variants_color');
            $table->index(['tenant_id', 'size'], 'idx_variants_size');

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign(['tenant_id', 'product_id'])->references(['tenant_id', 'id'])->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

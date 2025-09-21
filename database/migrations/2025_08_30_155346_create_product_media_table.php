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
        Schema::create('product_media', function (Blueprint $table) {
            $table->ulid('tenant_id');
            $table->ulid('id');
            $table->ulid('product_id');
            $table->ulid('product_variant_id')->nullable();
            $table->string('url', 512);
            $table->string('alt', 255)->nullable();
            $table->integer('position')->default(0);
            $table->timestamp('created_at', 6);

            $table->primary(['tenant_id', 'id']);
            $table->index(['tenant_id', 'product_id'], 'idx_media_product');
            $table->index(['tenant_id', 'product_variant_id'], 'idx_media_variant');
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign(['tenant_id', 'product_id'])->references(['tenant_id', 'id'])->on('products')->onDelete('cascade');
            $table->foreign(['tenant_id', 'product_variant_id'])->references(['tenant_id', 'id'])->on('product_variants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_media');
    }
};

<?php

use App\Enums\ProductStatus;
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
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('tenant_id');
            $table->ulid('id');
            $table->string('slug', 160);
            $table->string('title', 255);
            $table->string('brand', 120)->nullable();
            $table->text('description')->nullable();
            $table->json('attributes')->nullable();
            $table->enum('status', ProductStatus::values())->default('draft');
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();

            $table->primary(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'slug']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['tenant_id', 'brand', 'title'], 'idx_products_tenant_brand_title');
            $table->index(['tenant_id', 'status'], 'idx_products_tenant_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

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
        Schema::create('categories', function (Blueprint $table) {
            $table->ulid('tenant_id');
            $table->ulid('id');
            $table->ulid('parent_id')->nullable();
            $table->string('name', 160);
            $table->string('slug', 160);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();

            $table->primary(['tenant_id', 'id']);
            $table->unique(['tenant_id', 'slug'], 'uniq_categories_slug_per_tenant');
            $table->index(['tenant_id', 'parent_id'], 'idx_categories_parent');

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign(['tenant_id', 'parent_id'])->references(['tenant_id', 'id'])->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

<?php

use App\Enums\CouponType;
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
        Schema::create('coupons', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->string('code', 50);
            $table->string('name', 255);
            $table->enum('type', CouponType::values())->default('percent');
            $table->decimal('value', 10);
            $table->bigInteger('minimum_amount_cents')->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('valid_from', 6);
            $table->timestamp('valid_to', 6);
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();

            $table->primary(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');

            $table->index(['tenant_id', 'valid_from', 'valid_to'], 'idx_coupons_validity');
            $table->index(['is_active', 'valid_from', 'valid_to']);
            $table->index('usage_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

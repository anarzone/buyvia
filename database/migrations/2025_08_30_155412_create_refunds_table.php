<?php

use App\Enums\RefundStatus;
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
        Schema::create('refunds', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->ulid('payment_id', 16);
            $table->bigInteger('amount_cents');
            $table->char('currency', 3)->default('USD');
            $table->string('reason', 255)->nullable();
            $table->string('provider_refund_id', 255)->nullable();
            $table->enum('status', RefundStatus::values())->default('pending');
            $table->json('provider_data')->nullable();
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();

            $table->primary(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
            $table->foreign(['tenant_id', 'payment_id'])->references(['tenant_id', 'id'])->on('payments')->onDelete('cascade');

            $table->index('status');
            $table->index('provider_refund_id');
            $table->index(['tenant_id', 'payment_id'], 'idx_refund_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};

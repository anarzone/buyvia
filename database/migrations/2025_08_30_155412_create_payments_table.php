<?php

use App\Enums\PaymentStatus;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->ulid('order_id', 16);
            $table->string('provider', 50)->comment('stripe, paypal, ...');
            $table->string('provider_ref', 255)->nullable();
            $table->enum('status', PaymentStatus::values())->default('pending');
            $table->bigInteger('amount_cents');
            $table->char('currency', 3)->default('USD');
            $table->string('payment_method', 50)->nullable();
            $table->json('provider_data')->nullable();
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);
            $table->softDeletes();


            $table->primary(['tenant_id', 'id']);
            $table->index(['tenant_id', 'order_id'], 'idx_pay_order');

            $table->foreign('tenant_id')
                ->references('id')->on('tenants')
                ->onDelete('restrict');

            $table->foreign(['tenant_id', 'order_id'])
                ->references(['tenant_id', 'id'])->on('orders')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

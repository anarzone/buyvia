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
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16)->primary();
            $table->string('provider', 50);
            $table->string('event_type', 100);
            $table->string('event_id', 255)->nullable();
            $table->json('payload');
            $table->string('signature', 512)->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at', 6)->nullable();
            $table->timestamp('received_at', 6);
            $table->timestamp('created_at', 6);

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['provider', 'event_type']);
            $table->index('processed');
            $table->index('created_at');
            $table->index(['tenant_id', 'received_at'], 'idx_webhook_received');
            $table->unique(['provider', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};

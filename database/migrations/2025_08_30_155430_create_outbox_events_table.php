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
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16)->primary();
            $table->string('event_type', 128);
            $table->string('aggregate_type', 50)->comment('e.g. Order, Customer, ...');
            $table->string('aggregate_id', 100);
            $table->json('payload');
            $table->timestamp('occurred_at', 6);
            $table->timestamp('published_at', 6)->nullable();
            $table->timestamp('created_at', 6);

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['tenant_id', 'published_at', 'occurred_at'], 'idx_outbox_unpublished');
            $table->index(['published_at', 'occurred_at']);
            $table->index(['aggregate_type', 'aggregate_id']);
            $table->index('event_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
    }
};

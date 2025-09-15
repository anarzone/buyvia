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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16)->primary();
            $table->ulid('user_id', 16)->nullable();
            $table->string('entity', 100);
            $table->string('entity_id', 100);
            $table->string('action', 50);
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('created_at', 6);

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['tenant_id', 'created_at'], 'idx_audit_created');
            $table->index(['tenant_id', 'entity', 'entity_id'], 'idx_audit_entity');
            $table->index(['entity', 'entity_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};

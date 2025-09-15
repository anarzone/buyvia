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
        Schema::create('inventory_levels', function (Blueprint $table) {
            $table->ulid('tenant_id', 16);
            $table->string('sku', 64);
            $table->string('location_id', 32)->default('default');
            $table->integer('on_hand');
            $table->integer('reserved')->default(0);
            $table->timestamp('updated_at', 6);
            
            $table->primary(['tenant_id', 'sku', 'location_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_levels');
    }
};

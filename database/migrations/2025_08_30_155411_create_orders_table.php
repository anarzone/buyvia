<?php

use App\Enums\OrderStatus;
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
        Schema::create('orders', function (Blueprint $table) {
            // IDs
            $table->ulid('tenant_id', 16);
            $table->ulid('id', 16);
            $table->ulid('customer_id', 16)->nullable(); // NULL = guest

            // Business identity
            $table->string('order_number', 50); // unique per tenant

            // Contact (fast lookups)
            $table->string('contact_email', 254);
            $table->string('contact_name', 255)->nullable();
            $table->string('contact_phone', 40)->nullable();

            // Address snapshots (always filled)
            $table->json('shipping_address'); // {name,line1,line2,city,region,postal_code,country,phone,email}
            $table->json('billing_address');

            // Optional links to saved addresses (members)
            $table->ulid('shipping_address_id')->nullable();
            $table->ulid('billing_address_id')->nullable();

            // Money & status
            $table->char('currency', 3);
            $table->bigInteger('subtotal_cents');
            $table->bigInteger('discount_cents')->default(0);
            $table->bigInteger('tax_cents')->default(0);
            $table->bigInteger('shipping_cents')->default(0);
            $table->bigInteger('total_cents');

            $table->enum('status', OrderStatus::values())->default('pending');
            $table->timestamp('placed_at', 6)->nullable();
            $table->timestamp('paid_at', 6)->nullable();
            $table->timestamp('fulfilled_at', 6)->nullable();
            $table->timestamp('cancelled_at', 6)->nullable();

            // Misc
            $table->text('notes')->nullable();
            $table->timestamp('created_at', 6);
            $table->timestamp('updated_at', 6);

            // PK & uniqueness
            $table->primary(['tenant_id','id']);
            $table->unique(['tenant_id','order_number'], 'uniq_orders_tenant_number');

            // Helpful indexes
            $table->index(['tenant_id','customer_id','created_at'], 'idx_orders_tenant_customer_created');
            $table->index(['tenant_id','status','created_at'], 'idx_orders_tenant_status_created');
            $table->index(['tenant_id','contact_email','created_at'], 'idx_orders_tenant_email_created');

            $table->softDeletes();

            // Tenant-safe FKs (assuming customers/addresses are multi-tenant with PK (tenant_id,id))
            $table->foreign(['tenant_id','customer_id'])
                ->references(['tenant_id','id'])->on('customers')->onDelete('restrict');

            $table->foreign(['tenant_id','shipping_address_id'])
                ->references(['tenant_id','id'])->on('addresses')->onDelete('restrict');

            $table->foreign(['tenant_id','billing_address_id'])
                ->references(['tenant_id','id'])->on('addresses')->onDelete('restrict');
        });

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT chk_orders_totals
            CHECK (total_cents = subtotal_cents - discount_cents + tax_cents + shipping_cents)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

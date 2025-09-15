<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_number',
        'contact_email',
        'contact_name',
        'contact_phone',
        'shipping_address',
        'billing_address',
        'shipping_address_id',
        'billing_address_id',
        'currency',
        'subtotal_cents',
        'discount_cents',
        'tax_cents',
        'shipping_cents',
        'total_cents',
        'status',
        'placed_at',
        'paid_at',
        'fulfilled_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'subtotal_cents' => 'integer',
        'discount_cents' => 'integer',
        'tax_cents' => 'integer',
        'shipping_cents' => 'integer',
        'total_cents' => 'integer',
        'billing_address' => 'array',
        'shipping_address' => 'array',
        'placed_at' => 'datetime:Y-m-d H:i:s.u',
        'paid_at' => 'datetime:Y-m-d H:i:s.u',
        'fulfilled_at' => 'datetime:Y-m-d H:i:s.u',
        'cancelled_at' => 'datetime:Y-m-d H:i:s.u',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }
}

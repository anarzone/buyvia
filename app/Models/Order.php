<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use SoftDeletes, Compoships;

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
        'status' => OrderStatus::class,
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

    public function getKeyName(): array
    {
        return ['tenant_id', 'id'];
    }

    protected $primaryKey = ['tenant_id', 'id'];
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, ['tenant_id', 'customer_id'], ['tenant_id', 'id']);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, ['tenant_id', 'order_id'], ['tenant_id', 'id']);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, ['tenant_id', 'order_id'], ['tenant_id', 'id']);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, ['tenant_id', 'shipping_address_id'], ['tenant_id', 'id']);
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, ['tenant_id', 'billing_address_id'], ['tenant_id', 'id']);
    }
}

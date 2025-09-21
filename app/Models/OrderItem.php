<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    use SoftDeletes, Compoships;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'product_name',
        'product_sku',
        'product_variant_id',
        'quantity',
        'unit_price_cents',
        'tax_rate',
        'total_cents',
        'product_snapshot',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
        'tax_rate' => 'decimal:5',
        'total_cents' => 'integer',
        'product_snapshot' => 'array',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, ['tenant_id', 'order_id'], ['tenant_id', 'id']);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, ['tenant_id', 'product_variant_id'], ['tenant_id', 'id']);
    }
}

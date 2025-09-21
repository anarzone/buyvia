<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class InventoryReservation extends Model
{
    use Compoships;

    public $timestamps = true;

    const UPDATED_AT = null; // No updated_at in migration
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'tenant_id',
        'sku',
        'order_id',
        'quantity',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime:Y-m-d H:i:s.u',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];

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

    public function getKeyName(): array
    {
        return ['tenant_id', 'id'];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, ['tenant_id', 'sku'], ['tenant_id', 'sku']);
    }
}

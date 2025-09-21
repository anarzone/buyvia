<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductMedia extends Model
{
    use Compoships;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'product_variant_id',
        'url',
        'alt',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public $timestamps = true;

    const UPDATED_AT = null; // No updated_at in migration
    const CREATED_AT = 'created_at';

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, ['tenant_id', 'product_id'], ['tenant_id', 'id']);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, ['tenant_id', 'product_variant_id'], ['tenant_id', 'id']);
    }
}

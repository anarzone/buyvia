<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use SoftDeletes, Compoships;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'sku',
        'price_cents',
        'currency',
        'attr',
        'weight_g',
        'barcode',
        'is_active',
    ];

    protected $casts = [
        'price_cents' => 'integer',
        'weight_g' => 'integer',
        'attr' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function getKeyName(): array
    {
        return ['tenant_id', 'id'];
    }

    protected $primaryKey = ['tenant_id', 'id'];
    public $incrementing = false;

    protected $appends = [
        'color',
        'size'
    ];

    public function getColorAttribute()
    {
        return $this->attr['color'] ?? null;
    }

    public function getSizeAttribute()
    {
        return $this->attr['size'] ?? null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, ['tenant_id', 'product_id'], ['tenant_id', 'id']);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryLevels(): HasMany
    {
        return $this->hasMany(InventoryLevel::class, ['tenant_id', 'sku'], ['tenant_id', 'sku']);
    }

    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class, ['tenant_id', 'product_variant_id'], ['tenant_id', 'id']);
    }
}

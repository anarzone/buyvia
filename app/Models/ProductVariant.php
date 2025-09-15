<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasUlids, SoftDeletes;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryLevel(): HasMany
    {
        return $this->hasMany(InventoryLevel::class, 'sku', 'sku');
    }
}

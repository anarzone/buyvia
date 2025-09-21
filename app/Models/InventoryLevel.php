<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLevel extends Model
{
    use Compoships;

    protected $fillable = [
        'tenant_id',
        'sku',
        'location_id',
        'on_hand',
        'reserved',
    ];

    protected $casts = [
        'on_hand' => 'integer',
        'reserved' => 'integer',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $primaryKey = ['tenant_id', 'sku', 'location_id'];
    public $incrementing = false;
    public $timestamps = true;

    const CREATED_AT = null; // No created_at in migration
    const UPDATED_AT = 'updated_at';

    public function getKeyName(): array
    {
        return ['tenant_id', 'sku', 'location_id'];
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

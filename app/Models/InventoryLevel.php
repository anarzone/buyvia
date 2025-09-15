<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLevel extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'sku',
        'location',
        'on_hand',
        'reserved',
    ];

    protected $casts = [
        'on_hand' => 'integer',
        'reserved' => 'integer',
        'available' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'sku', 'sku');
    }
}

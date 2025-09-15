<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sku',
        'location',
        'type',
        'quantity',
        'reason',
        'reference_type',
        'reference_id',
        'metadata',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $dates = [
        'created_at',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'sku', 'sku');
    }
}

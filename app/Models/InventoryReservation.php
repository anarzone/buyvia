<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryReservation extends Model
{
    use HasUlids, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'sku',
        'location',
        'quantity',
        'reference_type',
        'reference_id',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime:Y-m-d H:i:s.u',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $dates = [
        'expires_at',
        'created_at',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'sku', 'sku');
    }
}

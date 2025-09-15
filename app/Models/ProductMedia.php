<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductMedia extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'product_id',
        'type',
        'url',
        'alt_text',
        'position',
        'metadata',
    ];

    protected $casts = [
        'position' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}

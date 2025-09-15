<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartSnapshot extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'session_id',
        'cart_data',
        'total_cents',
        'currency',
    ];

    protected $casts = [
        'cart_data' => 'array',
        'total_cents' => 'integer',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}

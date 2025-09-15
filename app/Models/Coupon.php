<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'minimum_amount_cents',
        'usage_limit',
        'used_count',
        'valid_from',
        'valid_to',
        'conditions',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_amount_cents' => 'integer',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'valid_from' => 'datetime:Y-m-d H:i:s.u',
        'valid_to' => 'datetime:Y-m-d H:i:s.u',
        'conditions' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];
}

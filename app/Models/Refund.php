<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'payment_id',
        'amount_cents',
        'currency',
        'reason',
        'provider_refund_id',
        'status',
        'provider_data',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
        'provider_data' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}

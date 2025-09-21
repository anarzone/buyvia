<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Refund extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'payment_id',
        'amount_cents',
        'currency',
        'reason',
        'provider_refund_id',
        'status',
        'provider_data',
    ];

    protected $casts = [
        'status' => RefundStatus::class,
        'amount_cents' => 'integer',
        'provider_data' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected function getKeyName()
    {
        return ['tenant_id', 'id'];
    }

    protected $primaryKey = ['tenant_id', 'id'];
    public $incrementing = false;

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }
}

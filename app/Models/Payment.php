<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'order_id',
        'provider',
        'provider_ref',
        'status',
        'amount_cents',
        'currency',
        'payment_method',
        'provider_data',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id', 'id')
                    ->where('tenant_id', $this->tenant_id);
    }
}

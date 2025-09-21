<?php

namespace App\Models;

use Awobaz\Compoships\Compoships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Customer extends Model
{
    use SoftDeletes, Compoships;

    protected $fillable = [
        'tenant_id',
        'email',
        'name',
        'phone',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s.u',
        'updated_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    public function getKeyName(): array
    {
        return ['tenant_id', 'id'];
    }

    protected $primaryKey = ['tenant_id', 'id'];
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, ['tenant_id', 'customer_id'], ['tenant_id', 'id']);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, ['tenant_id', 'customer_id'], ['tenant_id', 'id']);
    }

    public function cartSnapshots(): HasMany
    {
        return $this->hasMany(CartSnapshot::class, ['tenant_id', 'customer_id'], ['tenant_id', 'id']);
    }
}

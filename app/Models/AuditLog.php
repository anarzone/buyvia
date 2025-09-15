<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

    use SoftDeletes;
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'entity',
        'entity_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $dates = [
        'created_at',
    ];
}

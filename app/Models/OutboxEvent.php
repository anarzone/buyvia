<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class OutboxEvent extends Model
{
    use HasUlids, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'aggregate_type',
        'aggregate_id',
        'payload',
        'occurred_at',
        'published_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime:Y-m-d H:i:s.u',
        'published_at' => 'datetime:Y-m-d H:i:s.u',
        'created_at' => 'datetime:Y-m-d H:i:s.u',
    ];

    protected $dates = [
        'occurred_at',
        'published_at',
        'created_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'brand',
        'description',
        'attributes',
        'status',
    ];

    protected function casts()
    {
        return [
            'attributes' => 'array',
        ];
    }
}

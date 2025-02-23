<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceAggregate extends Model
{
    protected $fillable = [
        'pair',
        'price',
        'change_percentage',
        'highest',
        'lowest',
        'exchanges',
        'timestamp'
    ];
    
    protected $casts = [
        'price' => 'float',
        'change_percentage' => 'float',
        'highest' => 'float',
        'lowest' => 'float',
        'exchanges' => 'array',
        'timestamp' => 'datetime'
    ];

    protected $table = 'price_aggregate';
}

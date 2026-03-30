<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForecastSnapshot extends Model
{
    protected $fillable = [
        'type',
        'window_start',
        'window_end',
        'horizon_days',
        'data',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'window_start' => 'date',
            'window_end' => 'date',
            'generated_at' => 'datetime',
            'data' => 'array',
        ];
    }
}

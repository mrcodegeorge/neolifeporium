<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherInsight extends Model
{
    protected $fillable = [
        'region',
        'location',
        'weather_date',
        'summary',
        'rainfall_probability',
        'temperature_celsius',
        'alert_level',
        'recommendations',
        'source_payload',
    ];

    protected function casts(): array
    {
        return [
            'weather_date' => 'date',
            'recommendations' => 'array',
            'source_payload' => 'array',
        ];
    }
}

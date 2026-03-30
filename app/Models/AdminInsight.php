<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminInsight extends Model
{
    protected $fillable = [
        'type',
        'title',
        'message',
        'severity',
        'context',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'observed_at' => 'datetime',
        ];
    }
}

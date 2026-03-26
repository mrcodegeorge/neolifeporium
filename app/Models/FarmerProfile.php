<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'region',
        'district',
        'location',
        'farm_size_hectares',
        'crop_types',
        'primary_language',
    ];

    protected function casts(): array
    {
        return [
            'crop_types' => 'array',
            'farm_size_hectares' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

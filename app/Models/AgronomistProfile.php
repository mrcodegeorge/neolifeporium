<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgronomistProfile extends Model
{
    protected $fillable = [
        'user_id',
        'specialty',
        'experience_years',
        'bio',
        'hourly_rate',
        'regions_served',
        'is_available',
        'verification_status',
        'certification_document_path',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'regions_served' => 'array',
            'hourly_rate' => 'decimal:2',
            'is_available' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

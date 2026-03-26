<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'farmer_id',
        'agronomist_id',
        'scheduled_for',
        'duration_minutes',
        'session_type',
        'status',
        'amount',
        'topic',
        'notes',
        'meeting_link',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_for' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function agronomist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agronomist_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BookingMessage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ExpertReview::class);
    }
}

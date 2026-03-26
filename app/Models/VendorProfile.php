<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'product_category',
        'description',
        'region',
        'district',
        'verification_status',
        'verification_document_path',
        'verified_at',
        'commission_rate',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

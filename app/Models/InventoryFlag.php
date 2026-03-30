<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryFlag extends Model
{
    protected $fillable = [
        'product_id',
        'vendor_id',
        'type',
        'status',
        'details',
        'detected_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
}

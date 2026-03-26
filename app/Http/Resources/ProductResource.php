<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->sku,
            'type' => $this->product_type,
            'price' => $this->price,
            'currency' => $this->currency,
            'crop_type' => $this->crop_type,
            'region' => $this->region,
            'category' => $this->category?->only(['id', 'name', 'slug']),
            'vendor' => [
                'id' => $this->vendor?->id,
                'name' => $this->vendor?->vendorProfile?->business_name ?? $this->vendor?->name,
            ],
            'images' => $this->images->map(fn ($image) => [
                'path' => $image->path,
                'alt_text' => $image->alt_text,
                'is_primary' => $image->is_primary,
            ]),
            'rating' => round($this->reviews->avg('rating') ?? 0, 1),
            'reviews_count' => $this->reviews->count(),
            'description' => $this->description,
        ];
    }
}

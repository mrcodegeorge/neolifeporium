<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function paginateForMarketplace(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Product::query()
            ->with(['category', 'vendor.vendorProfile', 'images', 'reviews'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('crop_type', 'like', "%{$search}%");
            }))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->whereHas('category', fn ($builder) => $builder->where('slug', $category)))
            ->when($filters['crop_type'] ?? null, fn ($query, $cropType) => $query->where('crop_type', $cropType))
            ->when($filters['region'] ?? null, fn ($query, $region) => $query->where('region', $region))
            ->when($filters['min_price'] ?? null, fn ($query, $minPrice) => $query->where('price', '>=', $minPrice))
            ->when($filters['max_price'] ?? null, fn ($query, $maxPrice) => $query->where('price', '<=', $maxPrice))
            ->where('is_active', true)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function featured(int $limit = 6)
    {
        return Product::query()
            ->with(['images', 'category'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug): Product
    {
        return Product::query()
            ->with(['images', 'variants', 'reviews.user', 'vendor.vendorProfile', 'category'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }
}

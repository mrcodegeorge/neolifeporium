<?php

namespace App\Services\Marketplace;

use App\Models\Category;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductCatalogService
{
    public function __construct(private readonly ProductRepositoryInterface $products) {}

    public function marketplace(array $filters = [], int $perPage = 12)
    {
        return $this->products->paginateForMarketplace($filters, $perPage);
    }

    public function featured()
    {
        return $this->products->featured();
    }

    public function detail(string $slug)
    {
        return $this->products->findBySlug($slug);
    }

    public function categories()
    {
        return Category::query()->where('is_active', true)->with('children')->whereNull('parent_id')->get();
    }
}

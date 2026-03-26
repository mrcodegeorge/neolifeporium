<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function paginateForMarketplace(array $filters = [], int $perPage = 12): LengthAwarePaginator;

    public function featured(int $limit = 6);

    public function findBySlug(string $slug): Product;

    public function create(array $data): Product;
}

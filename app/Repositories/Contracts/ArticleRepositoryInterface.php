<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Support\Collection;

interface ArticleRepositoryInterface
{
    public function latestPublished(int $limit = 6): Collection;

    public function findBySlug(string $slug): Article;
}

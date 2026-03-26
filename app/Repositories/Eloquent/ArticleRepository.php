<?php

namespace App\Repositories\Eloquent;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Support\Collection;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function latestPublished(int $limit = 6): Collection
    {
        return Article::query()
            ->with('author')
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug): Article
    {
        return Article::query()
            ->with('author')
            ->where('slug', $slug)
            ->firstOrFail();
    }
}

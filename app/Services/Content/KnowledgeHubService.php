<?php

namespace App\Services\Content;

use App\Models\Article;
use App\Models\ArticleView;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class KnowledgeHubService
{
    public function __construct(private readonly ArticleRepositoryInterface $articles) {}

    public function latest(array $filters = [], int $perPage = 9): LengthAwarePaginator
    {
        return Article::query()
            ->with(['author', 'tags'])
            ->where('is_published', true)
            ->when($filters['category'] ?? null, function (Builder $query, string $category): void {
                $query->whereHas('tags', fn (Builder $inner) => $inner->where('slug', $category));
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('excerpt', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function detail(string $slug): Article
    {
        return Article::query()
            ->with(['author', 'tags', 'recommendedProducts.images', 'recommendedProducts.category'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function recordView(Article $article, ?User $user, ?string $ip, ?string $userAgent): void
    {
        ArticleView::query()->create([
            'article_id' => $article->id,
            'user_id' => $user?->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent ? str($userAgent)->limit(255)->toString() : null,
        ]);
    }

    public function categories(): Collection
    {
        return Tag::query()
            ->where('type', 'category')
            ->orderBy('name')
            ->get();
    }

    public function recommendedForFarmer(?User $user, int $limit = 6): Collection
    {
        if (! $user) {
            return Article::query()->where('is_published', true)->latest('published_at')->limit($limit)->get();
        }

        $cropTypes = collect($user->farmerProfile?->crop_types ?? [])->filter()->values();
        $region = $user->farmerProfile?->region;

        return Article::query()
            ->where('is_published', true)
            ->when($cropTypes->isNotEmpty(), fn (Builder $query) => $query->where(function (Builder $inner) use ($cropTypes): void {
                foreach ($cropTypes as $cropType) {
                    $inner->orWhereJsonContains('crop_tags', $cropType);
                }
            }))
            ->when($region, fn (Builder $query) => $query->where(function (Builder $inner) use ($region): void {
                $inner->whereNull('region_tags')->orWhereJsonContains('region_tags', $region);
            }))
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }
}

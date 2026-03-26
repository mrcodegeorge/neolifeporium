<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\User;
use App\Services\Marketplace\ProductCatalogService;

class MarketplaceController extends Controller
{
    public function __construct(private readonly ProductCatalogService $catalog) {}

    public function index()
    {
        return view('pages.marketplace', [
            'products' => $this->catalog->marketplace(request()->all(), 9),
            'categories' => $this->catalog->categories(),
        ]);
    }

    public function show(string $slug)
    {
        $product = $this->catalog->detail($slug);

        return view('pages.product-detail', [
            'product' => $product,
            'experts' => User::query()
                ->whereHas('roles', fn ($query) => $query->where('slug', 'agronomist'))
                ->with('agronomistProfile')
                ->limit(4)
                ->get(),
            'knowledgeArticles' => Article::query()
                ->where('is_published', true)
                ->where(function ($query) use ($product) {
                    $query->whereJsonContains('crop_tags', $product->crop_type)
                        ->orWhereHas('recommendedProducts', fn ($inner) => $inner->where('products.id', $product->id));
                })
                ->latest('published_at')
                ->limit(4)
                ->get(),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Product;
use App\Models\User;
use App\Services\Content\KnowledgeHubService;
use App\Services\Insights\WeatherInsightService;
use App\Services\Marketplace\ProductCatalogService;

class HomeController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalog,
        private readonly KnowledgeHubService $knowledgeHub,
        private readonly WeatherInsightService $weatherInsights
    ) {}

    public function index()
    {
        $featuredProducts = $this->catalog->featured();
        $articles = $this->knowledgeHub->latest();
        $weatherInsights = $this->weatherInsights->latestByRegion();

        return view('pages.home', [
            'featuredProducts' => $featuredProducts,
            'articles' => $articles,
            'weatherInsights' => $weatherInsights,
            'impactStats' => [
                ['value' => '40%', 'label' => 'potential yield lift from better-timed decisions', 'tone' => 'emerald'],
                ['value' => '30%', 'label' => 'less input waste with guided recommendations', 'tone' => 'gold'],
                ['value' => number_format(Product::query()->where('is_active', true)->count()), 'label' => 'agritech tools and services available', 'tone' => 'slate'],
                ['value' => number_format(User::query()->whereHas('roles', fn ($query) => $query->where('slug', 'agronomist'))->count()), 'label' => 'expert advisors ready to support farmers', 'tone' => 'forest'],
            ],
            'resultsStats' => [
                ['value' => '30%', 'label' => 'higher productivity through weather-aware action'],
                ['value' => '20%', 'label' => 'lower avoidable spending on wrong inputs'],
                ['value' => number_format(Booking::query()->count() + 1200), 'label' => 'farmer support touchpoints across advisory and commerce'],
            ],
            'innovationBlocks' => [
                [
                    'title' => 'Smart irrigation decisions',
                    'copy' => 'Spot the right irrigation tools early and match them to crop stage, season, and local operating reality.',
                    'image' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=1200&q=80',
                ],
                [
                    'title' => 'AI-guided recommendations',
                    'copy' => 'Combine crop type, weather patterns, and marketplace inventory to surface next-best actions for the field.',
                    'image' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=1200&q=80',
                ],
                [
                    'title' => 'Live weather intelligence',
                    'copy' => 'Translate forecasts into clear advice farmers can act on before rain, drought, or heat turns into losses.',
                    'image' => 'https://images.unsplash.com/photo-1473448912268-2022ce9509d8?auto=format&fit=crop&w=1200&q=80',
                ],
            ],
            'storySequence' => [
                [
                    'eyebrow' => 'Signal 01',
                    'title' => 'See the risk before it reaches the farm.',
                    'copy' => 'Weather patterns, crop stage, and location data surface early warnings so farmers can react before the wrong decision becomes expensive.',
                    'metric' => 'Rain and heat alerts',
                ],
                [
                    'eyebrow' => 'Signal 02',
                    'title' => 'Translate insight into the next best move.',
                    'copy' => 'Neolifeporium turns signals into practical action: what to buy, what to delay, what to ask an agronomist, and what to prioritize this week.',
                    'metric' => 'Product and advisory matches',
                ],
                [
                    'eyebrow' => 'Signal 03',
                    'title' => 'Act through one connected platform.',
                    'copy' => 'Browse tools, book advice, and track progress in a single journey built for mobile-first farming operations in Ghana and beyond.',
                    'metric' => 'Commerce, guidance, and follow-through',
                ],
            ],
        ]);
    }
}

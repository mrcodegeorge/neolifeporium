<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Services\Content\KnowledgeHubService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(private readonly KnowledgeHubService $knowledge) {}

    public function index(Request $request)
    {
        $articles = $this->knowledge->latest($request->only(['category', 'search']), (int) $request->integer('per_page', 9));

        return response()->json([
            'success' => true,
            'data' => $articles->items(),
            'meta' => [
                'current_page' => $articles->currentPage(),
                'last_page' => $articles->lastPage(),
                'per_page' => $articles->perPage(),
                'total' => $articles->total(),
            ],
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $article = $this->knowledge->detail($slug);
        $this->knowledge->recordView($article, $request->user(), $request->ip(), $request->userAgent());

        return response()->json([
            'success' => true,
            'data' => $article,
        ]);
    }

    public function recommended(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->knowledge->recommendedForFarmer($request->user()),
        ]);
    }
}

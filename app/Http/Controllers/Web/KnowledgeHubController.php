<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Content\KnowledgeHubService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KnowledgeHubController extends Controller
{
    public function __construct(private readonly KnowledgeHubService $knowledgeHub) {}

    public function index(Request $request): View
    {
        return view('knowledge.index', [
            'articles' => $this->knowledgeHub->latest($request->only(['search', 'category'])),
            'categories' => $this->knowledgeHub->categories(),
            'activeCategory' => $request->string('category')->toString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $article = $this->knowledgeHub->detail($slug);
        $this->knowledgeHub->recordView($article, $request->user(), $request->ip(), $request->userAgent());

        return view('knowledge.show', ['article' => $article]);
    }

    public function category(Request $request, string $slug): View
    {
        $request->merge(['category' => $slug]);

        return $this->index($request);
    }
}

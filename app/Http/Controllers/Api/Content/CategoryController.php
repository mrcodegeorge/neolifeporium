<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\KnowledgeHubService;

class CategoryController extends Controller
{
    public function __construct(private readonly KnowledgeHubService $knowledge) {}

    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => $this->knowledge->categories(),
        ]);
    }
}

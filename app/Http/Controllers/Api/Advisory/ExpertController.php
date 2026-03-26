<?php

namespace App\Http\Controllers\Api\Advisory;

use App\Http\Controllers\Controller;
use App\Services\Advisory\AdvisoryService;
use Illuminate\Http\Request;

class ExpertController extends Controller
{
    public function __construct(private readonly AdvisoryService $advisory) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->advisory->experts($request->only(['specialization', 'region'])),
        ]);
    }

    public function show(int $id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->advisory->expert($id),
        ]);
    }
}

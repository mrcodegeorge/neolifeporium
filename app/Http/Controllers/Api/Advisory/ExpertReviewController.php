<?php

namespace App\Http\Controllers\Api\Advisory;

use App\Http\Controllers\Controller;
use App\Services\Advisory\AdvisoryService;
use Illuminate\Http\Request;

class ExpertReviewController extends Controller
{
    public function __construct(private readonly AdvisoryService $advisory) {}

    public function store(Request $request, int $expertId)
    {
        $payload = $request->validate([
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->advisory->reviewExpert($expertId, $request->user()->id, $payload),
        ], 201);
    }
}

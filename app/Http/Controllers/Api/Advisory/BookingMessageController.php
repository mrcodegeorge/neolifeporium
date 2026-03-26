<?php

namespace App\Http\Controllers\Api\Advisory;

use App\Http\Controllers\Controller;
use App\Services\Advisory\AdvisoryService;
use Illuminate\Http\Request;

class BookingMessageController extends Controller
{
    public function __construct(private readonly AdvisoryService $advisory) {}

    public function index(Request $request, int $bookingId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->advisory->messages($bookingId, $request->user()->id),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->advisory->sendMessage((int) $payload['booking_id'], $request->user()->id, $payload['message']),
        ], 201);
    }
}

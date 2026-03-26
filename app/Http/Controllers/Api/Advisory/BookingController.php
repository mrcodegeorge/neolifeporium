<?php

namespace App\Http\Controllers\Api\Advisory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advisory\BookingRequest;
use App\Http\Resources\BookingResource;
use App\Services\Advisory\AdvisoryService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(private readonly AdvisoryService $advisory) {}

    public function store(BookingRequest $request)
    {
        return BookingResource::make($this->advisory->book([
            ...$request->validated(),
            'farmer_id' => $request->user()->id,
        ]));
    }

    public function index(Request $request)
    {
        $userId = (int) ($request->integer('user_id') ?: $request->user()->id);
        abort_unless($userId === $request->user()->id || $request->user()?->hasAnyRole(['admin', 'super_admin']), 403, 'Unauthorized booking history access.');

        return BookingResource::collection($this->advisory->upcomingForUser($userId));
    }
}

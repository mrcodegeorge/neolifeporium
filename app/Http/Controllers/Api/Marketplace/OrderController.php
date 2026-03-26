<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CheckoutRequest;
use App\Http\Requests\Orders\OrderStatusUpdateRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\Orders\OrderService;
use App\Support\ApiResponse;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function store(CheckoutRequest $request)
    {
        return OrderResource::make($this->orders->create($request->user()->id, $request->validated()));
    }

    public function history(int $farmerId)
    {
        abort_unless($farmerId === auth()->id() || auth()->user()?->hasAnyRole(['admin', 'super_admin']), 403, 'Unauthorized access to order history.');

        return OrderResource::collection($this->orders->history($farmerId));
    }

    public function updateStatus(OrderStatusUpdateRequest $request, Order $order)
    {
        $user = $request->user();
        $canModerate = $user->id === $order->vendor_id || $user->hasAnyRole(['admin', 'super_admin']);
        abort_unless($canModerate, 403, 'Unauthorized order update.');

        $updated = $this->orders->updateStatus($order, $request->validated('status'));

        return ApiResponse::success(OrderResource::make($updated->load(['items', 'payments'])), 'Order status updated.');
    }
}

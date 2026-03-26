<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function create(array $payload): Order
    {
        return Order::create($payload);
    }

    public function forFarmer(int $farmerId): Collection
    {
        return Order::query()
            ->with(['items.product.images', 'vendor.vendorProfile', 'payments'])
            ->where('farmer_id', $farmerId)
            ->latest()
            ->get();
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $order->update(['status' => $status]);

        return $order->refresh();
    }
}

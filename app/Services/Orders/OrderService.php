<?php

namespace App\Services\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly OrderRepositoryInterface $orders) {}

    public function create(int $farmerId, array $payload)
    {
        return DB::transaction(function () use ($payload) {
            $items = collect($payload['items']);
            $firstProduct = Product::query()->findOrFail($items->first()['product_id']);
            $subtotal = $items->sum(fn (array $item) => $item['quantity'] * $item['unit_price']);
            $commission = round($subtotal * 0.075, 2);
            $shipping = $payload['shipping_amount'] ?? 0;
            $tax = $payload['tax_amount'] ?? 0;

            $order = $this->orders->create([
                'order_number' => 'NLP-'.Str::upper(Str::random(10)),
                'farmer_id' => $farmerId,
                'vendor_id' => $firstProduct->vendor_id,
                'status' => OrderStatus::Pending->value,
                'subtotal' => $subtotal,
                'commission_amount' => $commission,
                'tax_amount' => $tax,
                'shipping_amount' => $shipping,
                'total_amount' => $subtotal + $shipping + $tax,
                'shipping_address' => $payload['shipping_address'] ?? [],
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return $order->load('items');
        });
    }

    public function history(int $farmerId)
    {
        return $this->orders->forFarmer($farmerId);
    }

    public function updateStatus(Order $order, string $status): Order
    {
        return $this->orders->updateStatus($order, $status);
    }
}

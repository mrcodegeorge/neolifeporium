<?php

namespace App\Services\Admin;

use App\Models\InventoryFlag;
use App\Models\Notification;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class InventoryIntelligenceService
{
    public function generate(): Collection
    {
        if (! Schema::hasTable('inventory_flags')) {
            return collect();
        }

        $flags = collect();
        $flags = $flags->merge($this->flagLowStock());
        $flags = $flags->merge($this->flagDeadStock());

        return $flags;
    }

    public function latest(int $limit = 8): Collection
    {
        if (! Schema::hasTable('inventory_flags')) {
            return collect();
        }

        return InventoryFlag::query()
            ->where('status', 'open')
            ->latest('detected_at')
            ->limit($limit)
            ->get();
    }

    private function flagLowStock(): Collection
    {
        $lowStock = Product::query()
            ->where('is_active', true)
            ->where('inventory', '<=', 10)
            ->get();

        return $lowStock->map(function (Product $product) {
            [$flag, $created] = $this->firstOrCreateFlag(
                [
                    'product_id' => $product->id,
                    'type' => 'low_stock',
                    'status' => 'open',
                ],
                [
                    'vendor_id' => $product->vendor_id,
                    'details' => ['inventory' => (int) $product->inventory],
                    'detected_at' => now(),
                ]
            );

            if ($created) {
                $this->notifyVendor($product, 'Low stock alert', "Inventory is down to {$product->inventory} units.");
            }

            return $flag;
        });
    }

    private function flagDeadStock(): Collection
    {
        if (! Schema::hasTable('order_items')) {
            return collect();
        }

        $since = now()->subDays(45);
        $soldProductIds = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.created_at', '>=', $since)
            ->distinct()
            ->pluck('order_items.product_id');

        $deadStock = Product::query()
            ->where('is_active', true)
            ->where('inventory', '>', 0)
            ->whereNotIn('id', $soldProductIds)
            ->get();

        return $deadStock->map(function (Product $product) use ($since) {
            [$flag, $created] = $this->firstOrCreateFlag(
                [
                    'product_id' => $product->id,
                    'type' => 'dead_stock',
                    'status' => 'open',
                ],
                [
                    'vendor_id' => $product->vendor_id,
                    'details' => ['since' => $since->toDateString(), 'inventory' => (int) $product->inventory],
                    'detected_at' => now(),
                ]
            );

            if ($created) {
                $this->notifyVendor($product, 'Dead stock detected', 'This item has had no sales in the last 45 days.');
            }

            return $flag;
        });
    }

    private function firstOrCreateFlag(array $attributes, array $values): array
    {
        $flag = InventoryFlag::query()->firstOrNew($attributes);
        if ($flag->exists) {
            return [$flag, false];
        }

        $flag->fill($values);
        $flag->save();

        return [$flag, true];
    }

    private function notifyVendor(Product $product, string $title, string $message): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Notification::query()->create([
            'user_id' => $product->vendor_id,
            'type' => 'inventory',
            'channel' => 'in_app',
            'title' => $title,
            'message' => "{$product->name}: {$message}",
            'payload' => [
                'product_id' => $product->id,
                'inventory' => (int) $product->inventory,
            ],
        ]);
    }
}

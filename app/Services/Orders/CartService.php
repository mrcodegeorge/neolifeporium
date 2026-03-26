<?php

namespace App\Services\Orders;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart_items';

    public function items(): Collection
    {
        return collect(session()->get(self::SESSION_KEY, []))->values();
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->items()->keyBy('product_id');
        $existing = $cart->get($product->id);

        $newQuantity = min($product->inventory, ($existing['quantity'] ?? 0) + $quantity);

        $cart->put($product->id, [
            'product_id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
            'quantity' => max(1, $newQuantity),
            'unit_price' => (float) $product->price,
            'line_total' => (float) $product->price * max(1, $newQuantity),
            'image' => $product->images->first()?->path,
        ]);

        session()->put(self::SESSION_KEY, $cart->values()->all());
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->items()->keyBy('product_id');

        if (! $cart->has($productId)) {
            return;
        }

        $item = $cart->get($productId);
        $quantity = max(1, $quantity);
        $item['quantity'] = $quantity;
        $item['line_total'] = $quantity * (float) $item['unit_price'];
        $cart->put($productId, $item);

        session()->put(self::SESSION_KEY, $cart->values()->all());
    }

    public function remove(int $productId): void
    {
        $cart = $this->items()->reject(fn (array $item) => $item['product_id'] === $productId)->values();
        session()->put(self::SESSION_KEY, $cart->all());
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function totals(): array
    {
        $subtotal = $this->items()->sum('line_total');
        $shipping = $subtotal > 0 ? 20.00 : 0;
        $tax = round($subtotal * 0.05, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => round($subtotal + $shipping + $tax, 2),
        ];
    }
}

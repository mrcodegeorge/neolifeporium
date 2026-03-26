<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $this->activeCart($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => $cart->load('items.product.images', 'items.product.vendor.vendorProfile'),
        ]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $cart = $this->activeCart($request->user()->id);
        $product = Product::query()->findOrFail((int) $payload['product_id']);
        $quantity = (int) ($payload['quantity'] ?? 1);

        $existing = $cart->items()->where('product_id', $product->id)->first();
        if ($existing) {
            $newQuantity = $existing->quantity + $quantity;
            $existing->update(['quantity' => $newQuantity, 'unit_price' => $product->price]);
        } else {
            $cart->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart.',
            'data' => $cart->fresh()->load('items.product.images'),
        ], 201);
    }

    public function update(Request $request, int $itemId)
    {
        $payload = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->activeCart($request->user()->id);
        $item = $cart->items()->findOrFail($itemId);
        $item->update(['quantity' => (int) $payload['quantity']]);

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated.',
            'data' => $cart->fresh()->load('items.product.images'),
        ]);
    }

    public function destroy(Request $request, int $itemId)
    {
        $cart = $this->activeCart($request->user()->id);
        $item = $cart->items()->findOrFail($itemId);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart item removed.',
            'data' => $cart->fresh()->load('items.product.images'),
        ]);
    }

    private function activeCart(int $userId): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => $userId, 'status' => 'active']
        );
    }
}

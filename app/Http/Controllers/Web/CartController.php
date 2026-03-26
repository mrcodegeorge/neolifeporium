<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\Orders\CartService;
use App\Services\Orders\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders
    ) {}

    public function index()
    {
        return view('pages.cart', [
            'items' => $this->cart->items(),
            'totals' => $this->cart->totals(),
        ]);
    }

    public function add(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()->with('images')->findOrFail($payload['product_id']);
        $this->cart->add($product, (int) ($payload['quantity'] ?? 1));

        return back()->with('status', 'Product added to cart.');
    }

    public function update(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $this->cart->update((int) $payload['product_id'], (int) $payload['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function remove(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'integer'],
        ]);

        $this->cart->remove((int) $payload['product_id']);

        return back()->with('status', 'Item removed.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'region' => ['required', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $items = $this->cart->items();
        abort_if($items->isEmpty(), 422, 'Cart is empty.');

        $farmer = auth()->user() ?: User::query()->firstOrCreate(
            ['phone' => $payload['phone']],
            [
                'name' => $payload['name'],
                'email' => $payload['email'] ?? null,
                'password' => Hash::make(Str::random(24)),
                'status' => 'active',
            ]
        );

        if (! $farmer->roles()->where('slug', RoleType::Farmer->value)->exists()) {
            $farmerRole = Role::query()->where('slug', RoleType::Farmer->value)->first();
            if ($farmerRole) {
                $farmer->roles()->attach($farmerRole->id);
            }
        }

        $totals = $this->cart->totals();

        $order = $this->orders->create($farmer->id, [
            'items' => $items->map(fn (array $item) => [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
            ])->all(),
            'shipping_address' => [
                'region' => $payload['region'],
                'district' => $payload['district'] ?? null,
                'location' => $payload['location'] ?? null,
            ],
            'shipping_amount' => $totals['shipping'],
            'tax_amount' => $totals['tax'],
            'notes' => 'Web checkout order',
        ]);

        $this->cart->clear();

        return redirect()->route('marketplace.index')->with('status', "Order {$order->order_number} placed successfully.");
    }
}

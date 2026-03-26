@extends('layouts.app', ['title' => 'Cart | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-end justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">Checkout flow</p>
            <h1 class="mt-2 text-3xl font-black text-palm">Cart and checkout</h1>
        </div>
        <a href="{{ route('marketplace.index') }}" class="rounded-full border border-slate-200 px-4 py-2 text-sm font-semibold">Continue shopping</a>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="space-y-4">
            @forelse($items as $item)
                <div class="rounded-3xl bg-white p-5 shadow-lg shadow-black/5">
                    <div class="flex gap-4">
                        <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="h-20 w-20 rounded-2xl object-cover">
                        <div class="flex-1">
                            <h2 class="text-lg font-bold">{{ $item['name'] }}</h2>
                            <p class="text-sm text-slate-500">SKU: {{ $item['sku'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-palm">GHS {{ number_format($item['unit_price'], 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-slate-500">Line total</p>
                            <p class="text-lg font-black text-palm">GHS {{ number_format($item['line_total'], 2) }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <form action="{{ route('cart.update') }}" method="POST" class="flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                            <input type="number" min="1" name="quantity" value="{{ $item['quantity'] }}" class="w-20 rounded-xl border-slate-200">
                            <button class="rounded-xl bg-leaf px-3 py-2 text-xs font-semibold text-white">Update</button>
                        </form>
                        <form action="{{ route('cart.remove') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="product_id" value="{{ $item['product_id'] }}">
                            <button class="rounded-xl border border-red-200 px-3 py-2 text-xs font-semibold text-red-600">Remove</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-3xl border border-slate-200 bg-white p-8 text-center">
                    <p class="text-lg font-semibold text-slate-700">Your cart is empty.</p>
                </div>
            @endforelse
        </div>

        <div class="space-y-5">
            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-palm">Order summary</h2>
                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between"><span>Subtotal</span><span>GHS {{ number_format($totals['subtotal'], 2) }}</span></div>
                    <div class="flex justify-between"><span>Shipping</span><span>GHS {{ number_format($totals['shipping'], 2) }}</span></div>
                    <div class="flex justify-between"><span>Tax</span><span>GHS {{ number_format($totals['tax'], 2) }}</span></div>
                </div>
                <div class="mt-4 border-t pt-4">
                    <div class="flex justify-between text-lg font-black text-palm">
                        <span>Total</span>
                        <span>GHS {{ number_format($totals['total'], 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <h2 class="text-xl font-black text-palm">Delivery details</h2>
                <form action="{{ route('checkout.store') }}" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <input name="name" placeholder="Full name" required class="w-full rounded-xl border-slate-200">
                    <input name="phone" placeholder="Phone number" required class="w-full rounded-xl border-slate-200">
                    <input type="email" name="email" placeholder="Email (optional)" class="w-full rounded-xl border-slate-200">
                    <input name="region" placeholder="Region" required class="w-full rounded-xl border-slate-200">
                    <input name="district" placeholder="District" class="w-full rounded-xl border-slate-200">
                    <input name="location" placeholder="Location details" class="w-full rounded-xl border-slate-200">
                    <button class="w-full rounded-full bg-palm px-4 py-3 text-sm font-semibold text-white" @disabled($items->isEmpty())>Place order</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

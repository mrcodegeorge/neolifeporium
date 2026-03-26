@extends('layouts.app', ['title' => 'Marketplace | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8" x-data="{ mobileFilters: false }">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">Marketplace</p>
            <h1 class="mt-2 text-3xl font-black text-palm">Agritech products, services, and tools</h1>
        </div>
        <button class="rounded-full bg-palm px-4 py-2 text-sm font-semibold text-white md:hidden" @click="mobileFilters = !mobileFilters">Filters</button>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[280px_1fr]">
        <aside class="rounded-3xl bg-white p-5 shadow-lg shadow-black/5" :class="{ 'block': mobileFilters, 'hidden md:block': !mobileFilters }">
            <form method="GET" class="space-y-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search crops, products, services" class="w-full rounded-2xl border-black/10">
                <input type="text" name="crop_type" value="{{ request('crop_type') }}" placeholder="Crop type" class="w-full rounded-2xl border-black/10">
                <input type="text" name="region" value="{{ request('region') }}" placeholder="Region" class="w-full rounded-2xl border-black/10">
                <div>
                    <label class="text-sm font-semibold">Category</label>
                    <select name="category" class="mt-1 w-full rounded-2xl border-black/10">
                        <option value="">All categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="w-full rounded-full bg-leaf px-4 py-3 text-sm font-semibold text-white">Apply filters</button>
            </form>
        </aside>

        <div>
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                @foreach($products as $product)
                    <div class="overflow-hidden rounded-3xl bg-white shadow-lg shadow-black/5">
                        <a href="{{ route('marketplace.show', $product->slug) }}">
                        <img src="{{ $product->images->first()?->path }}" alt="{{ $product->name }}" class="h-52 w-full object-cover">
                        </a>
                        <div class="p-5">
                            <div class="flex items-center justify-between text-xs uppercase tracking-[0.2em] text-slate-500">
                                <span>{{ $product->product_type }}</span>
                                <span>{{ $product->region }}</span>
                            </div>
                            <a href="{{ route('marketplace.show', $product->slug) }}" class="mt-2 block text-xl font-bold">{{ $product->name }}</a>
                            <p class="mt-2 text-sm text-slate-600">{{ $product->short_description }}</p>
                            <div class="mt-4 flex items-center justify-between">
                                <span class="text-lg font-black text-palm">GHS {{ number_format($product->price, 2) }}</span>
                                <span class="rounded-full bg-leaf/10 px-3 py-1 text-xs font-semibold text-leaf">{{ $product->crop_type }}</span>
                            </div>
                            <form action="{{ route('cart.add') }}" method="POST" class="mt-4">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button class="w-full rounded-full bg-leaf px-4 py-2 text-sm font-semibold text-white">Add to cart</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app', ['title' => $product->name.' | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-2">
        <div class="overflow-hidden rounded-3xl bg-white shadow-lg shadow-black/5">
            <img src="{{ $product->images->first()?->path }}" alt="{{ $product->name }}" class="h-[420px] w-full object-cover">
        </div>
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">{{ $product->category?->name }}</p>
            <h1 class="mt-2 text-4xl font-black text-palm">{{ $product->name }}</h1>
            <p class="mt-4 text-base text-slate-600">{{ $product->description }}</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold shadow">{{ $product->product_type }}</span>
                <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold shadow">{{ $product->crop_type }}</span>
                <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold shadow">{{ $product->region }}</span>
            </div>
            <div class="mt-8 rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
                <p class="text-sm text-slate-500">Vendor</p>
                <p class="text-lg font-bold">{{ $product->vendor?->vendorProfile?->business_name ?? $product->vendor?->name }}</p>
                <p class="mt-4 text-3xl font-black text-palm">GHS {{ number_format($product->price, 2) }}</p>
                <p class="mt-2 text-sm text-slate-500">Inventory: {{ $product->inventory }} units</p>
                <form action="{{ route('cart.add') }}" method="POST" class="mt-5 flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="number" min="1" max="{{ $product->inventory }}" name="quantity" value="1" class="w-24 rounded-xl border-slate-200">
                    <button class="rounded-full bg-leaf px-5 py-3 text-sm font-semibold text-white">Add to cart</button>
                </form>
                <a href="{{ route('advisory.index', ['specialization' => $product->crop_type]) }}" class="mt-4 inline-flex rounded-full border border-palm px-5 py-3 text-sm font-semibold text-palm">Talk to Expert</a>
            </div>
        </div>
    </div>

    <div class="mt-10 grid gap-6 lg:grid-cols-2">
        <section class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-2xl font-black text-palm">Recommended Experts</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @forelse($experts as $expert)
                    <a href="{{ route('advisory.show', $expert->id) }}" class="rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                        <p class="text-sm font-bold text-slate-900">{{ $expert->name }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $expert->agronomistProfile?->specialty }}</p>
                        <p class="mt-2 text-xs font-semibold uppercase tracking-[0.14em] text-palm">Book consultation</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500 sm:col-span-2">No experts available right now.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5">
            <h2 class="text-2xl font-black text-palm">Related Learning</h2>
            <div class="mt-4 space-y-3">
                @forelse($knowledgeArticles as $article)
                    <a href="{{ route('knowledge.show', $article->slug) }}" class="block rounded-2xl border border-slate-100 p-4 hover:bg-slate-50">
                        <p class="text-sm font-bold text-slate-900">{{ $article->title }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $article->excerpt }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">No related article yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</section>
@endsection

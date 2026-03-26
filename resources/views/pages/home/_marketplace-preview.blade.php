<section class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-leaf">Marketplace</p>
                <h2 class="mt-4 text-4xl font-black uppercase leading-[0.98] tracking-[-0.04em] text-slate-950 sm:text-5xl">When you're ready to act, the tools are here.</h2>
            </div>
            <a href="{{ route('marketplace.index') }}" class="rounded-full bg-slate-950 px-6 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-white transition hover:bg-palm">Explore Agritech Marketplace</a>
        </div>
        <div class="mt-12 grid gap-5 md:grid-cols-2 xl:grid-cols-4">
            @foreach($featuredProducts->take(4) as $product)
                <a href="{{ route('marketplace.show', $product->slug) }}" class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white transition hover:-translate-y-1 hover:shadow-2xl hover:shadow-black/10">
                    <img src="{{ $product->images->first()?->path }}" alt="{{ $product->name }}" loading="lazy" class="h-56 w-full object-cover">
                    <div class="p-5">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-leaf">{{ $product->category?->name }}</p>
                        <h3 class="mt-3 text-xl font-black uppercase leading-tight text-slate-950">{{ $product->name }}</h3>
                        <p class="mt-3 text-sm text-slate-600">{{ $product->short_description }}</p>
                        <div class="mt-5 flex items-center justify-between">
                            <span class="text-lg font-black text-palm">GHS {{ number_format($product->price, 2) }}</span>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">{{ $product->crop_type }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

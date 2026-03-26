<section class="bg-[#0a100b] py-20 text-white sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-grain">Innovation blocks</p>
            <h2 class="mt-4 text-4xl font-black uppercase leading-[0.98] tracking-[-0.04em] sm:text-5xl lg:text-6xl">Technology that respects how real farms operate.</h2>
        </div>
        <div class="mt-12 space-y-8">
            @foreach($innovationBlocks as $index => $block)
                <div class="grid gap-6 overflow-hidden rounded-[2rem] border border-white/10 bg-white/5 p-4 backdrop-blur sm:p-6 lg:grid-cols-2 {{ $index % 2 === 1 ? 'lg:[&>*:first-child]:order-2' : '' }}">
                    <div class="flex flex-col justify-center p-2 sm:p-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-grain">0{{ $index + 1 }}</p>
                        <h3 class="mt-4 text-3xl font-black uppercase leading-tight">{{ $block['title'] }}</h3>
                        <p class="mt-4 max-w-xl text-base leading-7 text-white/72">{{ $block['copy'] }}</p>
                    </div>
                    <div class="overflow-hidden rounded-[1.5rem]">
                        <img src="{{ $block['image'] }}" alt="{{ $block['title'] }}" loading="lazy" class="h-72 w-full object-cover sm:h-80">
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

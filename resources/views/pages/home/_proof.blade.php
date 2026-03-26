<section class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr]">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.35em] text-leaf">Proof and results</p>
                <h2 class="mt-4 text-4xl font-black uppercase leading-[0.98] tracking-[-0.04em] text-slate-950 sm:text-5xl">Better decisions show up in the field.</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach($resultsStats as $stat)
                    <div class="rounded-[2rem] bg-[#eff3ea] p-6">
                        <p class="text-5xl font-black tracking-[-0.05em] text-palm">{{ $stat['value'] }}</p>
                        <p class="mt-3 text-sm font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

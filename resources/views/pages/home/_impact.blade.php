<section class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-leaf">Impact</p>
            <h2 class="mt-4 text-4xl font-black uppercase leading-[0.98] tracking-[-0.04em] text-slate-950 sm:text-5xl">The numbers farmers feel before harvest ever begins.</h2>
        </div>
        <div class="grid gap-8 sm:grid-cols-2 xl:grid-cols-4">
            @foreach($impactStats as $stat)
                <div class="impact-panel impact-panel-{{ $stat['tone'] }}">
                    <div class="impact-panel-illustration">
                        <span class="impact-ring"></span>
                        <span class="impact-ring impact-ring-delayed"></span>
                        <span class="impact-bar impact-bar-one"></span>
                        <span class="impact-bar impact-bar-two"></span>
                    </div>
                    <div class="relative z-10">
                        <p class="text-5xl font-black tracking-[-0.05em] text-slate-950 sm:text-6xl">{{ $stat['value'] }}</p>
                        <p class="mt-4 max-w-xs text-sm font-medium uppercase tracking-[0.14em] text-slate-500">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

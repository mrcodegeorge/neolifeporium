<section class="bg-[#050805] py-20 text-white sm:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="text-sm font-semibold uppercase tracking-[0.35em] text-grain">Pinned scrollytelling</p>
            <h2 class="mt-4 text-4xl font-black uppercase leading-[0.98] tracking-[-0.04em] sm:text-5xl lg:text-6xl">A decision engine that stays in view while the story unfolds.</h2>
        </div>

        <div class="mt-14 grid gap-10 lg:grid-cols-[0.95fr_1.05fr]">
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="sequence-stage">
                    <div class="sequence-halo sequence-halo-one"></div>
                    <div class="sequence-halo sequence-halo-two"></div>
                    <div class="sequence-screen">
                        <div class="sequence-grid"></div>
                        <div class="sequence-orbit"></div>
                        <div class="sequence-column">
                            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-grain">Field intelligence layer</p>
                            <h3 class="mt-4 text-3xl font-black uppercase leading-tight sm:text-4xl">From uncertainty to action.</h3>
                            <p class="mt-4 max-w-md text-sm leading-7 text-white/68 sm:text-base">A cinematic view of weather, products, and advisory guidance working together so farmers can move with clarity.</p>
                        </div>
                        <div class="sequence-metrics">
                            <div class="sequence-chip">
                                <span class="sequence-dot bg-emerald-400"></span>
                                <span>Weather alerts</span>
                            </div>
                            <div class="sequence-chip">
                                <span class="sequence-dot bg-grain"></span>
                                <span>Input matching</span>
                            </div>
                            <div class="sequence-chip">
                                <span class="sequence-dot bg-sky-300"></span>
                                <span>Expert guidance</span>
                            </div>
                        </div>
                        <div class="sequence-panels">
                            <div class="sequence-panel sequence-panel-main">
                                <p class="text-xs uppercase tracking-[0.25em] text-white/45">Adaptive recommendation</p>
                                <p class="mt-2 text-xl font-black uppercase">Delay fertilizer application until after expected rainfall window.</p>
                            </div>
                            <div class="sequence-panel sequence-panel-secondary">
                                <p class="text-xs uppercase tracking-[0.25em] text-white/45">Suggested response</p>
                                <p class="mt-2 text-lg font-black uppercase">Switch to moisture-retention inputs and consult an agronomist.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:space-y-10">
                @foreach($storySequence as $step)
                    <article class="sequence-step">
                        <p class="text-xs font-semibold uppercase tracking-[0.35em] text-grain">{{ $step['eyebrow'] }}</p>
                        <h3 class="mt-4 text-3xl font-black uppercase leading-tight sm:text-4xl">{{ $step['title'] }}</h3>
                        <p class="mt-5 max-w-xl text-base leading-7 text-white/70 sm:text-lg">{{ $step['copy'] }}</p>
                        <div class="mt-6 inline-flex rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-white/68">
                            {{ $step['metric'] }}
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>

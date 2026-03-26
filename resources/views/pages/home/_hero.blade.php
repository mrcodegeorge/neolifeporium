<section class="relative isolate flex min-h-screen items-end overflow-hidden bg-black pt-28">
    <img src="https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1800&q=80" alt="African farmland with precision agriculture view" class="absolute inset-0 h-full w-full object-cover object-center opacity-40">
    <div class="hero-film-grain"></div>
    <div class="hero-light-beam"></div>
    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(5,10,7,0.28),rgba(5,10,7,0.92)_72%)]"></div>
    <div class="absolute right-[5%] top-28 hidden w-[25rem] overflow-hidden rounded-[2.2rem] border border-white/10 bg-white/10 shadow-2xl shadow-black/30 backdrop-blur xl:block 2xl:w-[30rem]">
        <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1200&q=80" alt="Close-up crop health scene" class="h-[24rem] w-full object-cover opacity-90 lg:h-[29rem]">
        <div class="absolute inset-x-0 bottom-0 bg-[linear-gradient(180deg,transparent,rgba(6,10,8,0.92))] p-6">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-grain">Real-time farming intelligence</p>
            <p class="mt-3 max-w-xs text-lg font-black uppercase text-white">A more cinematic view of yield, timing, and field action.</p>
        </div>
    </div>
    <div class="absolute inset-x-0 top-24 z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 xl:pr-[27rem] 2xl:pr-[32rem]">
        <div class="grid gap-3 sm:grid-cols-3">
            <div class="story-card animate-rise" style="animation-delay: 0.1s;">
                <p class="story-stat">40%</p>
                <p class="story-label">Potential yield lift when decisions stop relying on guesswork</p>
            </div>
            <div class="story-card animate-rise" style="animation-delay: 0.2s;">
                <p class="story-stat">30%</p>
                <p class="story-label">Less input waste with guided product and advisory choices</p>
            </div>
            <div class="story-card animate-rise" style="animation-delay: 0.3s;">
                <p class="story-stat">24/7</p>
                <p class="story-label">Weather, advisory, and commerce support in one workflow</p>
            </div>
        </div>
    </div>
    <div class="relative z-10 mx-auto flex w-full max-w-7xl items-end px-4 pb-14 sm:px-6 lg:px-8 lg:pb-20 xl:pr-[27rem] 2xl:pr-[32rem]">
        <div class="max-w-5xl">
            <p class="animate-rise text-xs font-semibold uppercase tracking-[0.45em] text-grain sm:text-sm">Science-backed agritech innovation for Ghana</p>
            <h1 class="animate-rise mt-4 max-w-4xl text-5xl font-black uppercase leading-[0.95] tracking-[-0.04em] text-white sm:text-6xl lg:text-8xl" style="animation-delay: 0.1s;">Smarter Farming Starts Here.</h1>
            <p class="animate-rise mt-6 max-w-2xl text-base text-white/72 sm:text-lg lg:text-xl" style="animation-delay: 0.2s;">Buy tools. Get insights. Increase your yield. Neolifeporium helps farmers make stronger decisions before weather, pricing, and timing turn into losses.</p>
            <div class="animate-rise mt-8 flex flex-col gap-3 sm:flex-row" style="animation-delay: 0.3s;">
                <a href="#solutions" class="rounded-full bg-white px-7 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-950 transition hover:scale-[1.02]">Explore Solutions</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-white/25 bg-white/10 px-7 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-white backdrop-blur transition hover:bg-white/15">Go to Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="rounded-full border border-white/25 bg-white/10 px-7 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-white backdrop-blur transition hover:bg-white/15">Join Now</a>
                @endauth
            </div>
        </div>
    </div>
</section>

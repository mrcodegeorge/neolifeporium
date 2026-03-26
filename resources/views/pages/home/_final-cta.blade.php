<section class="bg-white py-20 sm:py-28">
    <div class="mx-auto max-w-5xl px-4 text-center sm:px-6 lg:px-8">
        <p class="text-sm font-semibold uppercase tracking-[0.35em] text-leaf">Final call to action</p>
        <h2 class="mt-4 text-4xl font-black uppercase leading-[0.98] tracking-[-0.04em] text-slate-950 sm:text-5xl lg:text-6xl">Start farming smarter today.</h2>
        <p class="mx-auto mt-6 max-w-2xl text-base leading-7 text-slate-600 sm:text-lg">Join Neolifeporium to explore solutions, talk to experts, and turn uncertainty into better farm performance.</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-full bg-slate-950 px-7 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-white">Go to Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="rounded-full bg-slate-950 px-7 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-white">Join Now</a>
            @endauth
            <a href="{{ route('marketplace.index') }}" class="rounded-full border border-slate-200 px-7 py-4 text-center text-sm font-bold uppercase tracking-[0.2em] text-slate-950">Browse Solutions</a>
        </div>
    </div>
</section>

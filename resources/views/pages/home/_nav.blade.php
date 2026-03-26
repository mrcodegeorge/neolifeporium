<header class="fixed inset-x-0 top-0 z-50">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-5 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="text-lg font-black tracking-[0.08em] text-white sm:text-xl">NEOLIFEPORIUM</a>
        <div class="hidden items-center gap-8 text-sm font-medium text-white/80 md:flex">
            <a href="{{ route('marketplace.index') }}" class="transition hover:text-white">Marketplace</a>
            <a href="#solutions" class="transition hover:text-white">Solutions</a>
            @auth
                <a href="{{ route('dashboard') }}" class="transition hover:text-white">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-white">Login</a>
            @endauth
        </div>
        <a href="{{ route('marketplace.index') }}" class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white backdrop-blur transition hover:bg-white hover:text-slate-900">Explore</a>
    </nav>
</header>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Neolifeporium' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Agritech marketplace and farmer intelligence hub for Ghana and Africa.' }}">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-mist text-slate-900">
    <div class="{{ $pageShellClass ?? 'min-h-screen bg-[radial-gradient(circle_at_top,_rgba(215,178,109,0.25),_transparent_35%),linear-gradient(180deg,_#f7f3ea_0%,_#eef4e8_100%)]' }}">
        @if(($showDefaultNav ?? true) === true)
            <header class="{{ $headerClass ?? 'border-b border-black/5 bg-white/85 backdrop-blur' }}">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('home') }}" class="text-xl font-black tracking-tight text-palm">Neolifeporium</a>
                    <div class="hidden gap-6 text-sm font-medium md:flex">
                        <a href="{{ route('marketplace.index') }}">Marketplace</a>
                        <a href="{{ route('knowledge.index') }}">Knowledge Hub</a>
                        <a href="{{ route('advisory.index') }}">Advisory</a>
                        <a href="{{ route('cart.index') }}">Cart ({{ collect(session('cart_items', []))->sum('quantity') }})</a>
                        @auth
                            <a href="{{ route('dashboard.redirect') }}">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}">Login</a>
                        @endauth
                    </div>
                    <div class="flex items-center gap-3">
                        @auth
                            @if(auth()->user()->roles()->count() > 1)
                                <form method="POST" action="{{ route('roles.switch') }}" class="hidden md:block">
                                    @csrf
                                    <select name="role" onchange="this.form.submit()" class="rounded-full border border-slate-300 bg-white px-3 py-2 text-xs font-semibold uppercase tracking-[0.08em] text-slate-700">
                                        @foreach(auth()->user()->roles()->pluck('slug') as $roleSlug)
                                            <option value="{{ $roleSlug }}" @selected(session('active_role') === $roleSlug)>{{ str($roleSlug)->replace('_', ' ')->title() }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            @endif
                            <a href="{{ route('roles.onboarding') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                Roles
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                    Logout
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:text-slate-900">
                                Login
                            </a>
                        @endauth
                    </div>
                </nav>
            </header>
        @endif

        <main>
            @if (session('status'))
                <div class="mx-auto mt-6 max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('status') }}
                    </div>
                </div>
            @endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
</body>
</html>

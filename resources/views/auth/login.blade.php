@extends('layouts.app', [
    'title' => 'Login | Neolifeporium',
    'showDefaultNav' => false,
    'pageShellClass' => 'min-h-screen bg-[#f1effb]',
])

@section('content')
<section class="relative overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute -left-28 -top-28 h-72 w-72 rounded-full bg-[#5f33dc]/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 h-80 w-80 rounded-full bg-[#6d40ee]/20 blur-3xl"></div>

    <div class="mx-auto grid w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-[0_30px_70px_rgba(34,21,84,0.22)] md:grid-cols-2">
        <div class="relative hidden bg-[#fbfaff] p-10 md:flex md:flex-col md:justify-between">
            <a href="{{ route('home') }}" class="inline-flex text-2xl font-black tracking-tight text-[#3d2f8f]">Neolifeporium</a>

            <div class="space-y-5">
                <h2 class="text-4xl font-black leading-tight text-[#2e255d]">Grow Smarter With Trusted Agritech Decisions.</h2>
                <p class="max-w-md text-sm text-[#5c5680]">Real-time product intelligence, weather insight, and expert support built for African farming operations.</p>
            </div>

            <div class="rounded-2xl border border-[#dacfff] bg-white p-4">
                <svg viewBox="0 0 520 320" class="h-56 w-full">
                    <rect x="0" y="0" width="520" height="320" fill="#fbfaff"></rect>
                    <circle cx="120" cy="100" r="44" fill="#ece5ff"></circle>
                    <circle cx="402" cy="88" r="54" fill="#ece5ff"></circle>
                    <rect x="208" y="174" width="92" height="86" rx="14" fill="#f0e7ff"></rect>
                    <rect x="228" y="146" width="50" height="28" rx="8" fill="#d8c7ff"></rect>
                    <circle cx="114" cy="140" r="30" fill="#5f33dc"></circle>
                    <rect x="84" y="168" width="62" height="92" rx="22" fill="#5f33dc"></rect>
                    <circle cx="382" cy="132" r="34" fill="#5f33dc"></circle>
                    <rect x="350" y="168" width="66" height="92" rx="24" fill="#5f33dc"></rect>
                    <path d="M144 190L230 196" stroke="#5f33dc" stroke-width="6" stroke-linecap="round"></path>
                    <path d="M334 188L294 196" stroke="#5f33dc" stroke-width="6" stroke-linecap="round"></path>
                    <circle cx="256" cy="98" r="32" fill="#ffffff" stroke="#5f33dc" stroke-width="3" stroke-dasharray="7 7"></circle>
                    <circle cx="256" cy="98" r="10" fill="#d8c7ff"></circle>
                    <path d="M254 109V124" stroke="#5f33dc" stroke-width="4" stroke-linecap="round"></path>
                    <path d="M244 261H280" stroke="#8b69f3" stroke-width="8" stroke-linecap="round"></path>
                </svg>
            </div>
        </div>

        <div class="bg-gradient-to-b from-[#6c3deb] to-[#5726d8] p-8 text-white sm:p-10">
            <h1 class="text-5xl font-black leading-none">Welcome!</h1>
            <p class="mt-3 text-sm text-white/80">Sign in to continue to your dashboard.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-10 space-y-4">
                @csrf

                <div>
                    <label for="login" class="sr-only">Email or Phone</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus placeholder="Email or phone number" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                    @error('login')
                        <p class="mt-2 text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required placeholder="Password" class="w-full rounded-full border border-white/20 bg-white px-5 py-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 outline-none transition focus:border-[#ffcb43] focus:ring-2 focus:ring-[#ffcb43]/35">
                    @error('password')
                        <p class="mt-2 text-sm font-medium text-[#ffd4d4]">{{ $message }}</p>
                    @enderror
                </div>

                <label class="inline-flex items-center gap-2 pt-1 text-xs font-medium text-white/80">
                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-white/40 text-[#5928dd] focus:ring-[#ffcb43]/60">
                    Remember me on this device
                </label>

                <div class="pt-3">
                    <div class="mb-4 h-1.5 w-full rounded-full bg-[#7b59e9]">
                        <div class="h-full w-2/3 rounded-full bg-[#ffcb43]"></div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('register') }}" class="inline-flex min-w-40 items-center justify-center rounded-full bg-[#ffcb43] px-6 py-3 text-sm font-bold text-[#2c1f68] transition hover:bg-[#ffd566]">
                            Create account
                        </a>
                        <button type="submit" class="inline-flex min-w-32 items-center justify-center rounded-full border border-white/60 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                            Sign in
                        </button>
                    </div>
                </div>
            </form>

            <p class="mt-8 text-sm text-white/75">
                Need a new account?
                <a href="{{ route('register') }}" class="font-semibold text-[#ffcb43] hover:text-[#ffe092]">Register here</a>
            </p>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app', [
    'title' => 'Choose Role | Neolifeporium',
    'showDefaultNav' => false,
    'pageShellClass' => 'min-h-screen bg-[#f1effb]',
])

@section('content')
<section class="relative overflow-hidden px-4 py-10 sm:px-6 lg:px-8">
    <div class="pointer-events-none absolute -left-28 -top-28 h-72 w-72 rounded-full bg-[#5f33dc]/15 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -right-24 h-80 w-80 rounded-full bg-[#6d40ee]/20 blur-3xl"></div>

    <div class="mx-auto grid w-full max-w-6xl overflow-hidden rounded-2xl bg-white shadow-[0_30px_70px_rgba(34,21,84,0.22)] md:grid-cols-2">
        <div class="hidden bg-[#fbfaff] p-10 md:flex md:flex-col md:justify-between">
            <a href="{{ route('home') }}" class="inline-flex text-2xl font-black tracking-tight text-[#3d2f8f]">Neolifeporium</a>
            <div class="space-y-4">
                <h1 class="text-4xl font-black leading-tight text-[#2e255d]">Choose Your Active Workspace.</h1>
                <p class="max-w-md text-sm text-[#5c5680]">Switch instantly between Farmer, Vendor, and Expert experiences from one account.</p>
            </div>
            <div class="rounded-full bg-[#ece5ff] px-4 py-2 text-xs font-bold uppercase tracking-[0.18em] text-[#5f33dc]">
                Role access control
            </div>
        </div>

        <div class="bg-gradient-to-b from-[#6c3deb] to-[#5726d8] p-8 text-white sm:p-10">
            <h2 class="text-5xl font-black leading-none">Welcome!</h2>
            <p class="mt-3 text-sm text-white/80">Select the role you want to operate now.</p>

            <div class="mt-8 grid gap-3">
                @foreach($roles as $roleSlug)
                    <form method="POST" action="{{ route('roles.switch') }}">
                        @csrf
                        <input type="hidden" name="role" value="{{ $roleSlug }}">
                        <button type="submit" class="w-full rounded-2xl border p-4 text-left transition {{ $activeRole === $roleSlug ? 'border-[#ffcb43] bg-white/20' : 'border-white/25 bg-white/10 hover:bg-white/15' }}">
                            <p class="text-lg font-bold">{{ str($roleSlug)->replace('_', ' ')->title() }}</p>
                            <p class="mt-1 text-xs uppercase tracking-[0.15em] {{ $activeRole === $roleSlug ? 'text-[#ffcb43]' : 'text-white/70' }}">
                                {{ $activeRole === $roleSlug ? 'Current role' : 'Switch to this role' }}
                            </p>
                        </button>
                    </form>
                @endforeach
            </div>

            <a href="{{ route('dashboard.redirect') }}" class="mt-6 inline-flex min-w-36 items-center justify-center rounded-full border border-white/60 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                Back to dashboard
            </a>
        </div>
    </div>
</section>
@endsection

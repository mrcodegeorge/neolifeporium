@extends('layouts.app', [
    'title' => 'Login | Neolifeporium',
])

@section('content')
<section class="mx-auto max-w-md px-4 py-14 sm:px-6 lg:px-0">
    <div class="rounded-3xl bg-white p-7 shadow-lg shadow-black/5 sm:p-8">
        <h1 class="text-3xl font-black text-palm">Login</h1>
        <p class="mt-2 text-sm text-slate-600">Sign in with your email or phone number.</p>

        <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="login" class="text-sm font-semibold text-slate-700">Email or Phone</label>
                <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                @error('login')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="text-sm font-semibold text-slate-700">Password</label>
                <input id="password" name="password" type="password" required class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-900 outline-none transition focus:border-palm/60 focus:ring-2 focus:ring-palm/15">
                @error('password')
                    <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300 text-palm focus:ring-palm/30">
                Remember me
            </label>

            <button type="submit" class="w-full rounded-full bg-slate-950 px-6 py-3 text-sm font-bold uppercase tracking-[0.15em] text-white transition hover:bg-palm">
                Sign In
            </button>
        </form>

        <p class="mt-6 text-sm text-slate-600">
            No account yet?
            <a href="{{ route('register') }}" class="font-semibold text-palm hover:text-leaf">Create one</a>
        </p>
    </div>
</section>
@endsection

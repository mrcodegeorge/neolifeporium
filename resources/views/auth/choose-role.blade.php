@extends('layouts.app', ['title' => 'Choose Role | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-0">
    <div class="rounded-3xl bg-white p-8 shadow-lg shadow-black/5">
        <h1 class="text-3xl font-black text-slate-900">Switch Workspace Role</h1>
        <p class="mt-2 text-sm text-slate-600">Select the role you want to operate with right now.</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            @foreach($roles as $roleSlug)
                <form method="POST" action="{{ route('roles.switch') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $roleSlug }}">
                    <button type="submit" class="w-full rounded-2xl border border-slate-200 p-4 text-left transition hover:border-palm/60 hover:bg-slate-50">
                        <p class="text-lg font-bold text-slate-900">{{ str($roleSlug)->replace('_', ' ')->title() }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.15em] {{ $activeRole === $roleSlug ? 'text-emerald-600' : 'text-slate-500' }}">
                            {{ $activeRole === $roleSlug ? 'Current role' : 'Switch to this role' }}
                        </p>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</section>
@endsection

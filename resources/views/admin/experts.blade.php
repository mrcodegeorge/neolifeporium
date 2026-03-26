@extends('layouts.app', ['title' => 'Admin Experts | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Administration</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Expert Verification</h1>
        </div>
        <a href="{{ route('admin.panel') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to Dashboard</a>
    </div>

    <form method="GET" action="{{ route('admin.experts') }}" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 shadow-lg shadow-black/5 sm:grid-cols-3">
        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search expert or specialty" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
        <select name="verification_status" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">All statuses</option>
            @foreach(['pending','approved','rejected'] as $status)
                <option value="{{ $status }}" @selected($filters['verification_status'] === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply Filters</button>
    </form>

    <div class="mt-6 space-y-4">
        @forelse($experts as $expert)
            <article class="rounded-3xl bg-white p-5 shadow-lg shadow-black/5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-lg font-bold text-slate-900">{{ $expert->user?->name }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $expert->specialty }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $expert->bio ?: 'No bio submitted.' }}</p>
                    </div>
                    <form action="{{ route('admin.experts.status', $expert) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="verification_status" class="rounded-xl border-slate-200 text-sm">
                            @foreach(['pending','approved','rejected'] as $status)
                                <option value="{{ $status }}" @selected($expert->verification_status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Save</button>
                    </form>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $expert->verification_status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($expert->verification_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ ucfirst($expert->verification_status) }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ (int) $expert->experience_years }} years experience</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">GHS {{ number_format((float) $expert->hourly_rate, 2) }}/session</span>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-white p-8 text-center text-slate-500 shadow-lg shadow-black/5">
                No expert applications found.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $experts->links() }}</div>
</section>
@endsection

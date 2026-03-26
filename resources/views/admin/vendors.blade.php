@extends('layouts.app', ['title' => 'Admin Vendors | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Administration</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900">Vendor Verification</h1>
        </div>
        <a href="{{ route('admin.panel') }}" class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Back to Dashboard</a>
    </div>

    <form method="GET" action="{{ route('admin.vendors') }}" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 shadow-lg shadow-black/5 sm:grid-cols-2 lg:grid-cols-5">
        <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search business or owner" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
        <select name="verification_status" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">All statuses</option>
            @foreach(['pending','approved','rejected'] as $status)
                <option value="{{ $status }}" @selected($filters['verification_status'] === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="region" class="rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-palm/60">
            <option value="">All regions</option>
            @foreach($regions as $region)
                <option value="{{ $region }}" @selected($filters['region'] === $region)>{{ $region }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Apply Filters</button>
        <a href="{{ route('admin.vendors') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">Reset</a>
    </form>

    <div class="mt-6 space-y-4">
        @forelse($vendors as $vendor)
            <article class="rounded-3xl bg-white p-5 shadow-lg shadow-black/5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="text-lg font-bold text-slate-900">{{ $vendor->business_name }}</p>
                        <p class="mt-1 text-xs uppercase tracking-[0.16em] text-slate-500">{{ $vendor->user?->name }} · {{ $vendor->region ?? 'No region' }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $vendor->description ?: 'No vendor description yet.' }}</p>
                    </div>
                    <form action="{{ route('admin.vendors.status', $vendor) }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <select name="verification_status" class="rounded-xl border-slate-200 text-sm">
                            @foreach(['pending','approved','rejected'] as $status)
                                <option value="{{ $status }}" @selected($vendor->verification_status === $status)>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                        <button class="rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white">Save</button>
                    </form>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $vendor->verification_status === 'approved' ? 'bg-emerald-100 text-emerald-700' : ($vendor->verification_status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ ucfirst($vendor->verification_status) }}
                    </span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Commission {{ number_format($vendor->commission_rate, 2) }}%</span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Submitted {{ $vendor->created_at?->diffForHumans() }}</span>
                </div>
            </article>
        @empty
            <div class="rounded-3xl bg-white p-8 text-center text-slate-500 shadow-lg shadow-black/5">
                No vendors match the selected filters.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $vendors->links() }}</div>
</section>
@endsection

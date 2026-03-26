@extends('layouts.app', ['title' => 'Advisory Booking | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-leaf">Advisory system</p>
    <h1 class="mt-2 text-3xl font-black text-palm">Book trusted agronomists for chat-first support</h1>

    <form method="GET" class="mt-6 grid gap-3 rounded-3xl bg-white p-4 shadow-lg shadow-black/5 md:grid-cols-4">
        <input type="text" name="specialization" value="{{ request('specialization') }}" placeholder="Specialization (e.g. maize)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
        <input type="text" name="region" value="{{ request('region') }}" placeholder="Region" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
        <button class="rounded-xl bg-palm px-4 py-2 text-sm font-semibold text-white">Filter Experts</button>
        <a href="{{ route('advisory.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">Reset</a>
    </form>

    <div class="mt-6 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($experts as $expert)
            <a href="{{ route('advisory.show', $expert['id']) }}" class="rounded-3xl bg-white p-6 shadow-lg shadow-black/5 transition hover:-translate-y-1 hover:shadow-xl">
                <p class="text-sm font-semibold text-leaf">{{ $expert['specializations']->implode(', ') ?: 'General Agronomy' }}</p>
                <h2 class="mt-2 text-2xl font-bold">{{ $expert['name'] }}</h2>
                <p class="mt-3 text-sm text-slate-600">{{ $expert['bio'] }}</p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-lg font-black text-palm">GHS {{ number_format($expert['pricing'], 2) }}/hr</span>
                    <span class="rounded-full bg-leaf/10 px-3 py-1 text-xs font-semibold text-leaf">
                        {{ $expert['rating'] ? "{$expert['rating']}★" : 'No ratings' }}
                    </span>
                </div>
            </a>
        @empty
            <div class="rounded-3xl bg-white p-8 text-slate-500 shadow-lg shadow-black/5 md:col-span-2 xl:col-span-3">No experts found for this filter.</div>
        @endforelse
    </div>
</section>
@endsection

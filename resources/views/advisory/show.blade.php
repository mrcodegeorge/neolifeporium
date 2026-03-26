@extends('layouts.app', ['title' => $expert['name'].' | Advisory | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
    <a href="{{ route('advisory.index') }}" class="text-sm font-semibold text-palm">&larr; Back to experts</a>
    <div class="mt-4 grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
        <div class="rounded-3xl bg-white p-7 shadow-lg shadow-black/5">
            <p class="text-sm uppercase tracking-[0.2em] text-leaf">{{ $expert['specializations']->implode(', ') ?: 'General Agronomy' }}</p>
            <h1 class="mt-2 text-4xl font-black text-palm">{{ $expert['name'] }}</h1>
            <p class="mt-4 text-slate-600">{{ $expert['bio'] }}</p>
            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Rate</p>
                    <p class="mt-2 text-xl font-black text-palm">GHS {{ number_format($expert['pricing'], 2) }}/hr</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Rating</p>
                    <p class="mt-2 text-xl font-black text-palm">{{ $expert['rating'] ?? 'N/A' }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Availability</p>
                    <p class="mt-2 text-xl font-black text-palm">{{ $expert['availability'] ? 'Online' : 'Offline' }}</p>
                </div>
            </div>

            <h2 class="mt-8 text-xl font-black text-slate-900">Farmer Reviews</h2>
            <div class="mt-3 space-y-3">
                @forelse($expert['reviews'] as $review)
                    <div class="rounded-2xl border border-slate-100 p-4">
                        <p class="text-sm font-semibold text-slate-900">{{ $review['farmer'] ?? 'Farmer' }} · {{ $review['rating'] }}/5</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $review['comment'] ?? 'No comment' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No reviews yet.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-white p-7 shadow-lg shadow-black/5">
            <h2 class="text-2xl font-black text-palm">Book Session</h2>
            @auth
                <form method="POST" action="{{ route('advisory.book', $expert['id']) }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Date and time</label>
                        <input type="datetime-local" name="scheduled_for" required class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Duration (minutes)</label>
                        <select name="duration_minutes" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            @foreach([30,45,60,90] as $minutes)
                                <option value="{{ $minutes }}">{{ $minutes }} min</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Session type</label>
                        <select name="session_type" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
                            <option value="chat">Chat (Phase 1)</option>
                            <option value="video">Video (Phase 2 ready)</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Topic</label>
                        <input type="text" name="topic" required class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm" placeholder="e.g. Maize leaf yellowing">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm"></textarea>
                    </div>
                    <button class="w-full rounded-xl bg-palm px-4 py-3 text-sm font-semibold text-white">Pay & Book Consultation</button>
                </form>
            @else
                <p class="mt-4 text-sm text-slate-600">Please login to continue booking.</p>
                <a href="{{ route('login') }}" class="mt-4 inline-flex rounded-xl bg-palm px-4 py-2 text-sm font-semibold text-white">Login</a>
            @endauth
        </div>
    </div>
</section>
@endsection

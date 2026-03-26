@extends('layouts.app', ['title' => 'Booking Chat | Neolifeporium'])

@section('content')
<section class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.14em] text-slate-500">Booking #{{ $booking->id }}</p>
            <h1 class="text-3xl font-black text-palm">{{ $booking->topic }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $booking->farmer?->name }} with {{ $booking->agronomist?->name }} · {{ optional($booking->scheduled_for)->format('M d, Y H:i') }}</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-slate-600">{{ $booking->status }}</span>
    </div>

    <div class="mt-6 rounded-3xl bg-white p-5 shadow-lg shadow-black/5">
        <div class="max-h-[430px] space-y-3 overflow-y-auto pr-2">
            @forelse($messages as $message)
                <div class="rounded-2xl border border-slate-100 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">{{ $message->sender?->name }} · {{ optional($message->created_at)->format('M d H:i') }}</p>
                    <p class="mt-2 text-sm text-slate-700">{{ $message->message }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500">No messages yet. Start the conversation.</p>
            @endforelse
        </div>
        <form method="POST" action="{{ route('advisory.messages.send', $booking) }}" class="mt-4 flex items-center gap-3">
            @csrf
            <input type="text" name="message" required placeholder="Type your message" class="flex-1 rounded-xl border border-slate-200 px-4 py-3 text-sm">
            <button class="rounded-xl bg-palm px-5 py-3 text-sm font-semibold text-white">Send</button>
        </form>
    </div>
</section>
@endsection

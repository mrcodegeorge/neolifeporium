<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\Advisory\AdvisoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdvisoryController extends Controller
{
    public function __construct(private readonly AdvisoryService $advisory) {}

    public function index(Request $request): View
    {
        return view('advisory.index', [
            'experts' => $this->advisory->experts($request->only(['specialization', 'region'])),
        ]);
    }

    public function show(int $expertId): View
    {
        return view('advisory.show', [
            'expert' => $this->advisory->expert($expertId),
        ]);
    }

    public function book(Request $request, int $expertId): RedirectResponse
    {
        $request->validate([
            'scheduled_for' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'session_type' => ['required', 'in:chat,video'],
            'topic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $expert = User::query()->whereHas('roles', fn ($query) => $query->where('slug', 'agronomist'))->findOrFail($expertId);

        $booking = $this->advisory->book([
            'farmer_id' => $request->user()->id,
            'agronomist_id' => $expert->id,
            'scheduled_for' => $request->string('scheduled_for')->toString(),
            'duration_minutes' => $request->integer('duration_minutes'),
            'session_type' => $request->string('session_type')->toString(),
            'amount' => (float) ($expert->agronomistProfile?->hourly_rate ?? 0),
            'topic' => $request->string('topic')->toString(),
            'notes' => $request->string('notes')->toString() ?: null,
            'payment_provider' => 'paystack',
        ]);

        return redirect()->route('advisory.chat', $booking)->with('status', 'Booking submitted. Complete payment to confirm.');
    }

    public function chat(Booking $booking, Request $request): View
    {
        abort_unless(
            in_array($request->user()->id, [$booking->farmer_id, $booking->agronomist_id], true)
                || $request->user()->hasAnyRole(['admin', 'super_admin']),
            403
        );

        return view('advisory.chat', [
            'booking' => $booking->load('farmer', 'agronomist.agronomistProfile'),
            'messages' => $this->advisory->messages($booking->id, $request->user()->id),
        ]);
    }

    public function sendMessage(Booking $booking, Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $this->advisory->sendMessage($booking->id, $request->user()->id, $payload['message']);

        return back()->with('status', 'Message sent.');
    }
}

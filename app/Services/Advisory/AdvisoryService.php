<?php

namespace App\Services\Advisory;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingMessage;
use App\Models\ExpertReview;
use App\Models\ExpertSpecialization;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdvisoryService
{
    public function __construct(private readonly BookingRepositoryInterface $bookings) {}

    public function experts(array $filters = []): Collection
    {
        return User::query()
            ->with(['agronomistProfile', 'expertSpecializations', 'expertReviews'])
            ->whereHas('roles', fn (Builder $query) => $query->where('slug', 'agronomist'))
            ->when($filters['specialization'] ?? null, function (Builder $query, string $specialization): void {
                $query->whereHas('expertSpecializations', fn (Builder $inner) => $inner->where('name', 'like', "%{$specialization}%"));
            })
            ->when($filters['region'] ?? null, function (Builder $query, string $region): void {
                $query->whereHas('agronomistProfile', fn (Builder $inner) => $inner->whereJsonContains('regions_served', $region));
            })
            ->get()
            ->map(function (User $expert) {
                $averageRating = round((float) $expert->expertReviews->avg('rating'), 2);

                return [
                    'id' => $expert->id,
                    'name' => $expert->name,
                    'specializations' => $expert->expertSpecializations->pluck('name')->values(),
                    'bio' => $expert->agronomistProfile?->bio,
                    'experience' => $expert->agronomistProfile?->specialty,
                    'pricing' => (float) ($expert->agronomistProfile?->hourly_rate ?? 0),
                    'regions' => $expert->agronomistProfile?->regions_served ?? [],
                    'rating' => $averageRating > 0 ? $averageRating : null,
                    'reviews_count' => $expert->expertReviews->count(),
                ];
            });
    }

    public function expert(int $expertId): array
    {
        $expert = User::query()
            ->with(['agronomistProfile', 'expertSpecializations', 'expertReviews.farmer'])
            ->whereHas('roles', fn (Builder $query) => $query->where('slug', 'agronomist'))
            ->findOrFail($expertId);

        $averageRating = round((float) $expert->expertReviews->avg('rating'), 2);

        return [
            'id' => $expert->id,
            'name' => $expert->name,
            'specializations' => $expert->expertSpecializations->pluck('name')->values(),
            'bio' => $expert->agronomistProfile?->bio,
            'experience' => $expert->agronomistProfile?->specialty,
            'pricing' => (float) ($expert->agronomistProfile?->hourly_rate ?? 0),
            'regions' => $expert->agronomistProfile?->regions_served ?? [],
            'availability' => (bool) ($expert->agronomistProfile?->is_available ?? false),
            'rating' => $averageRating > 0 ? $averageRating : null,
            'reviews' => $expert->expertReviews->map(fn (ExpertReview $review) => [
                'rating' => $review->rating,
                'comment' => $review->comment,
                'farmer' => $review->farmer?->name,
                'created_at' => $review->created_at?->toDateTimeString(),
            ])->values(),
        ];
    }

    public function book(array $payload): Booking
    {
        return DB::transaction(function () use ($payload): Booking {
            $booking = $this->bookings->create([
                ...$payload,
                'status' => BookingStatus::Pending->value,
            ]);

            if (($payload['amount'] ?? 0) > 0) {
                Payment::query()->create([
                    'booking_id' => $booking->id,
                    'user_id' => $payload['farmer_id'],
                    'provider' => $payload['payment_provider'] ?? 'paystack',
                    'provider_reference' => $payload['payment_reference'] ?? 'BK-'.$booking->id.'-'.str()->upper(str()->random(8)),
                    'amount' => $payload['amount'],
                    'currency' => 'GHS',
                    'status' => 'pending',
                    'channel' => 'advisory',
                    'payload' => $payload['payment_payload'] ?? [],
                ]);
            }

            return $booking->load('farmer', 'agronomist');
        });
    }

    public function upcomingForUser(int $userId): Collection
    {
        return $this->bookings->upcomingForUser($userId);
    }

    public function messages(int $bookingId, int $userId): Collection
    {
        $booking = Booking::query()->findOrFail($bookingId);
        abort_unless(in_array($userId, [$booking->farmer_id, $booking->agronomist_id], true), 403);

        return BookingMessage::query()
            ->with('sender')
            ->where('booking_id', $bookingId)
            ->orderBy('created_at')
            ->get();
    }

    public function sendMessage(int $bookingId, int $senderId, string $message): BookingMessage
    {
        $booking = Booking::query()->findOrFail($bookingId);
        abort_unless(in_array($senderId, [$booking->farmer_id, $booking->agronomist_id], true), 403);

        return BookingMessage::query()->create([
            'booking_id' => $bookingId,
            'sender_id' => $senderId,
            'message' => $message,
        ]);
    }

    public function reviewExpert(int $expertId, int $farmerId, array $payload): ExpertReview
    {
        return ExpertReview::query()->create([
            'booking_id' => $payload['booking_id'] ?? null,
            'expert_id' => $expertId,
            'farmer_id' => $farmerId,
            'rating' => $payload['rating'],
            'comment' => $payload['comment'] ?? null,
        ]);
    }

    public function ensureExpertSpecializations(int $expertId, array $specializations): void
    {
        foreach ($specializations as $name) {
            ExpertSpecialization::query()->firstOrCreate([
                'expert_id' => $expertId,
                'name' => $name,
            ]);
        }
    }
}

<?php

namespace App\Repositories\Eloquent;

use App\Models\Booking;
use App\Repositories\Contracts\BookingRepositoryInterface;
use Illuminate\Support\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $payload): Booking
    {
        return Booking::create($payload);
    }

    public function upcomingForUser(int $userId): Collection
    {
        return Booking::query()
            ->with(['farmer', 'agronomist.agronomistProfile'])
            ->where(fn ($query) => $query->where('farmer_id', $userId)->orWhere('agronomist_id', $userId))
            ->orderBy('scheduled_for')
            ->get();
    }
}

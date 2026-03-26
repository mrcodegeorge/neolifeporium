<?php

namespace App\Repositories\Contracts;

use App\Models\Booking;
use Illuminate\Support\Collection;

interface BookingRepositoryInterface
{
    public function create(array $payload): Booking;

    public function upcomingForUser(int $userId): Collection;
}

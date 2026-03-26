<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function create(array $payload): Order;

    public function forFarmer(int $farmerId): Collection;

    public function updateStatus(Order $order, string $status): Order;
}

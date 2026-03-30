<?php

namespace App\Services\Admin;

use App\Models\AdminAlert;
use App\Models\Order;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminAlertService
{
    public function generate(): Collection
    {
        if (! Schema::hasTable('admin_alerts')) {
            return collect();
        }

        $alerts = collect();

        $revenueAlert = $this->revenueDropAlert();
        if ($revenueAlert !== null) {
            $alerts->push($revenueAlert);
        }

        $paymentAlert = $this->failedPaymentAlert();
        if ($paymentAlert !== null) {
            $alerts->push($paymentAlert);
        }

        $orderSpikeAlert = $this->orderSpikeAlert();
        if ($orderSpikeAlert !== null) {
            $alerts->push($orderSpikeAlert);
        }

        return $alerts->map(fn (array $payload) => AdminAlert::create($payload));
    }

    public function latest(int $limit = 6): Collection
    {
        if (! Schema::hasTable('admin_alerts')) {
            return collect();
        }

        return AdminAlert::query()
            ->whereNull('resolved_at')
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function revenueDropAlert(): ?array
    {
        $today = now()->startOfDay();
        $yesterday = $today->copy()->subDay();

        $todayRevenue = (float) Order::query()->whereBetween('created_at', [$today, $today->copy()->endOfDay()])->sum('total_amount');
        $yesterdayRevenue = (float) Order::query()->whereBetween('created_at', [$yesterday, $yesterday->copy()->endOfDay()])->sum('total_amount');

        if ($yesterdayRevenue <= 0) {
            return null;
        }

        $delta = round((($todayRevenue - $yesterdayRevenue) / $yesterdayRevenue) * 100, 1);
        if ($delta >= -15) {
            return null;
        }

        return [
            'type' => 'revenue_drop',
            'title' => 'Revenue drop detected',
            'message' => "Revenue is down {$delta}% compared to yesterday.",
            'severity' => $delta < -30 ? 'critical' : 'warning',
            'context' => [
                'today' => $todayRevenue,
                'yesterday' => $yesterdayRevenue,
                'delta_percent' => $delta,
            ],
        ];
    }

    private function failedPaymentAlert(): ?array
    {
        if (! Schema::hasTable('payments')) {
            return null;
        }

        $since = now()->subHours(12);
        $failedCount = Payment::query()
            ->where('status', 'failed')
            ->where('created_at', '>=', $since)
            ->count();

        if ($failedCount < 3) {
            return null;
        }

        return [
            'type' => 'failed_payments',
            'title' => 'Payment failures spiking',
            'message' => "{$failedCount} failed payments in the last 12 hours.",
            'severity' => $failedCount >= 7 ? 'critical' : 'warning',
            'context' => [
                'failed_count' => $failedCount,
                'since' => $since->toDateTimeString(),
            ],
        ];
    }

    private function orderSpikeAlert(): ?array
    {
        $today = now()->startOfDay();
        $lastHour = now()->subHour();

        $hourlyCount = Order::query()->where('created_at', '>=', $lastHour)->count();
        $dailyAverage = (int) DB::query()
            ->fromSub(function ($query) use ($today) {
                $query->from('orders')
                    ->where('created_at', '>=', $today->copy()->subDays(7))
                    ->selectRaw('DATE(created_at) as day')
                    ->selectRaw('COUNT(*) as daily_count')
                    ->groupBy('day');
            }, 'daily')
            ->selectRaw('AVG(daily_count) as avg_daily')
            ->value('avg_daily') ?: 0;

        if ($dailyAverage <= 0) {
            return null;
        }

        $threshold = max(5, (int) round($dailyAverage * 0.25));
        if ($hourlyCount < $threshold) {
            return null;
        }

        return [
            'type' => 'order_spike',
            'title' => 'Order spike detected',
            'message' => "{$hourlyCount} orders were placed in the last hour.",
            'severity' => $hourlyCount >= $threshold * 2 ? 'warning' : 'info',
            'context' => [
                'hourly_count' => $hourlyCount,
                'threshold' => $threshold,
            ],
        ];
    }
}

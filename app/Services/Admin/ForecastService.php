<?php

namespace App\Services\Admin;

use App\Models\ForecastSnapshot;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ForecastService
{
    public function generateRevenueForecast(int $horizonDays = 14): ?ForecastSnapshot
    {
        if (! Schema::hasTable('forecast_snapshots')) {
            return null;
        }

        $windowEnd = now()->startOfDay();
        $windowStart = $windowEnd->copy()->subDays(13);

        $series = $this->dailyRevenueSeries($windowStart, $windowEnd);
        if ($series->isEmpty()) {
            return null;
        }

        $forecast = $this->linearForecast($series, $horizonDays);

        return ForecastSnapshot::create([
            'type' => 'revenue',
            'window_start' => $windowStart->toDateString(),
            'window_end' => $windowEnd->toDateString(),
            'horizon_days' => $horizonDays,
            'data' => [
                'history' => $series->values(),
                'forecast' => $forecast,
            ],
            'generated_at' => now(),
        ]);
    }

    public function generateUserGrowthForecast(int $horizonMonths = 6): ?ForecastSnapshot
    {
        if (! Schema::hasTable('forecast_snapshots')) {
            return null;
        }

        $windowEnd = now()->startOfMonth();
        $windowStart = $windowEnd->copy()->subMonths(5);
        $series = $this->monthlyUserSeries($windowStart, $windowEnd);

        if ($series->isEmpty()) {
            return null;
        }

        $forecast = $this->linearForecast($series, $horizonMonths, 'month');

        return ForecastSnapshot::create([
            'type' => 'user_growth',
            'window_start' => $windowStart->toDateString(),
            'window_end' => $windowEnd->toDateString(),
            'horizon_days' => $horizonMonths * 30,
            'data' => [
                'history' => $series->values(),
                'forecast' => $forecast,
            ],
            'generated_at' => now(),
        ]);
    }

    public function latest(string $type): ?ForecastSnapshot
    {
        if (! Schema::hasTable('forecast_snapshots')) {
            return null;
        }

        return ForecastSnapshot::query()
            ->where('type', $type)
            ->latest('generated_at')
            ->first();
    }

    private function dailyRevenueSeries(Carbon $from, Carbon $to): Collection
    {
        $data = Order::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(total_amount) as total')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::parse($row->day)->format('M d'),
                'value' => (float) $row->total,
            ]);

        return $data;
    }

    private function monthlyUserSeries(Carbon $from, Carbon $to): Collection
    {
        return User::query()
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
            ->selectRaw('COUNT(*) as total')
            ->whereBetween('created_at', [$from, $to->copy()->endOfMonth()])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->month)->format('M Y'),
                'value' => (int) $row->total,
            ]);
    }

    private function linearForecast(Collection $series, int $horizon, string $unit = 'day'): array
    {
        $points = $series->values()->map(fn ($row, $index) => [
            'x' => $index + 1,
            'y' => (float) $row['value'],
        ]);

        $n = max(1, $points->count());
        $sumX = $points->sum('x');
        $sumY = $points->sum('y');
        $sumXY = $points->sum(fn ($p) => $p['x'] * $p['y']);
        $sumX2 = $points->sum(fn ($p) => $p['x'] * $p['x']);

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        $slope = $denominator !== 0.0 ? (($n * $sumXY) - ($sumX * $sumY)) / $denominator : 0.0;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        $lastIndex = $n;
        $forecast = [];
        for ($i = 1; $i <= $horizon; $i++) {
            $value = max(0, $intercept + $slope * ($lastIndex + $i));
            $label = $unit === 'month'
                ? now()->startOfMonth()->addMonths($i)->format('M Y')
                : now()->startOfDay()->addDays($i)->format('M d');

            $forecast[] = [
                'label' => $label,
                'value' => round($value, 2),
            ];
        }

        return $forecast;
    }
}

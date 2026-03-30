<?php

namespace App\Services\Admin;

use App\Models\AdminInsight;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminInsightService
{
    public function generate(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        if (! Schema::hasTable('admin_insights')) {
            return collect();
        }

        $toDate = $to?->copy() ?? now()->endOfDay();
        $fromDate = $from?->copy() ?? $toDate->copy()->subDays(6)->startOfDay();
        $previousFrom = $fromDate->copy()->subDays($fromDate->diffInDays($toDate) + 1);
        $previousTo = $fromDate->copy()->subDay()->endOfDay();

        $insights = collect();

        $categoryDrop = $this->categoryDropInsight($fromDate, $toDate, $previousFrom, $previousTo);
        if ($categoryDrop !== null) {
            $insights->push($categoryDrop);
        }

        $vendorOutperformance = $this->vendorOutperformanceInsight($fromDate, $toDate);
        if ($vendorOutperformance !== null) {
            $insights->push($vendorOutperformance);
        }

        $regionSpike = $this->regionSpikeInsight($fromDate, $toDate);
        if ($regionSpike !== null) {
            $insights->push($regionSpike);
        }

        $growthInsight = $this->userGrowthInsight($fromDate, $toDate);
        if ($growthInsight !== null) {
            $insights->push($growthInsight);
        }

        return $insights->map(function (array $payload) {
            return AdminInsight::create($payload);
        });
    }

    public function latest(int $limit = 6): Collection
    {
        if (! Schema::hasTable('admin_insights')) {
            return collect();
        }

        return AdminInsight::query()
            ->latest('observed_at')
            ->limit($limit)
            ->get();
    }

    private function categoryDropInsight(Carbon $fromDate, Carbon $toDate, Carbon $previousFrom, Carbon $previousTo): ?array
    {
        if (! Schema::hasTable('order_items')) {
            return null;
        }

        $current = $this->orderRevenueByCategory($fromDate, $toDate);
        $previous = $this->orderRevenueByCategory($previousFrom, $previousTo);

        if ($current->isEmpty() || $previous->isEmpty()) {
            return null;
        }

        $dropCandidate = $current->map(function ($value, $category) use ($previous) {
            $prev = $previous->get($category, 0);
            if ($prev <= 0) {
                return null;
            }
            $delta = round((($value - $prev) / $prev) * 100, 1);
            return ['category' => $category, 'delta' => $delta, 'current' => $value, 'previous' => $prev];
        })->filter(fn ($row) => $row !== null && $row['delta'] < -10)->sortBy('delta')->first();

        if (! $dropCandidate) {
            return null;
        }

        return [
            'type' => 'category_drop',
            'title' => 'Category revenue dip detected',
            'message' => "{$dropCandidate['category']} revenue dropped {$dropCandidate['delta']}% vs last period.",
            'severity' => $dropCandidate['delta'] < -25 ? 'critical' : 'warning',
            'context' => $dropCandidate,
            'observed_at' => $toDate,
        ];
    }

    private function vendorOutperformanceInsight(Carbon $fromDate, Carbon $toDate): ?array
    {
        $topVendor = Order::query()
            ->select('vendor_id', DB::raw('SUM(total_amount) as total'))
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('vendor_id')
            ->orderByDesc('total')
            ->first();

        if (! $topVendor || $topVendor->total <= 0) {
            return null;
        }

        $average = (float) Order::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->average('total_amount');

        $vendor = User::query()->find($topVendor->vendor_id);
        if (! $vendor) {
            return null;
        }

        $multiplier = $average > 0 ? round(($topVendor->total / $average), 2) : 0;

        return [
            'type' => 'vendor_outperformance',
            'title' => 'Vendor outperforming peers',
            'message' => "{$vendor->name} delivered GHS ".number_format((float) $topVendor->total, 2)." in sales.",
            'severity' => $multiplier >= 2 ? 'info' : 'warning',
            'context' => [
                'vendor_id' => $vendor->id,
                'vendor_name' => $vendor->name,
                'total' => (float) $topVendor->total,
                'avg_order_value' => $average,
                'multiplier' => $multiplier,
            ],
            'observed_at' => $toDate,
        ];
    }

    private function regionSpikeInsight(Carbon $fromDate, Carbon $toDate): ?array
    {
        if (! Schema::hasTable('farmer_profiles')) {
            return null;
        }

        $regionCounts = Order::query()
            ->join('farmer_profiles', 'farmer_profiles.user_id', '=', 'orders.farmer_id')
            ->select('farmer_profiles.region', DB::raw('COUNT(*) as total'))
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->whereNotNull('farmer_profiles.region')
            ->groupBy('farmer_profiles.region')
            ->orderByDesc('total')
            ->first();

        if (! $regionCounts || $regionCounts->total < 5) {
            return null;
        }

        return [
            'type' => 'regional_demand_spike',
            'title' => 'Regional demand spike',
            'message' => "{$regionCounts->region} generated {$regionCounts->total} orders in the current range.",
            'severity' => 'info',
            'context' => [
                'region' => $regionCounts->region,
                'orders' => (int) $regionCounts->total,
            ],
            'observed_at' => $toDate,
        ];
    }

    private function userGrowthInsight(Carbon $fromDate, Carbon $toDate): ?array
    {
        $newUsers = User::query()->whereBetween('created_at', [$fromDate, $toDate])->count();
        if ($newUsers < 5) {
            return null;
        }

        return [
            'type' => 'user_growth',
            'title' => 'User growth momentum',
            'message' => "{$newUsers} new users joined during this period.",
            'severity' => 'info',
            'context' => [
                'new_users' => $newUsers,
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'observed_at' => $toDate,
        ];
    }

    private function orderRevenueByCategory(Carbon $fromDate, Carbon $toDate): Collection
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('products')) {
            return collect();
        }

        return OrderItem::query()
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->groupBy('categories.name')
            ->select('categories.name', DB::raw('SUM(order_items.line_total) as total'))
            ->pluck('total', 'categories.name')
            ->map(fn ($value) => (float) $value);
    }
}

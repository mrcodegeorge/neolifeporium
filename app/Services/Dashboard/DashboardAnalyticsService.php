<?php

namespace App\Services\Dashboard;

use App\Models\Article;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\WeatherInsight;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardAnalyticsService
{
    public function admin(User $user, ?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate, $previousFrom, $previousTo] = $this->resolveRange($from, $to);

        $revenueCurrent = (float) Order::query()->whereBetween('created_at', [$fromDate, $toDate])->sum('total_amount');
        $revenuePrevious = (float) Order::query()->whereBetween('created_at', [$previousFrom, $previousTo])->sum('total_amount');
        $ordersCurrent = Order::query()->whereBetween('created_at', [$fromDate, $toDate])->count();
        $ordersPrevious = Order::query()->whereBetween('created_at', [$previousFrom, $previousTo])->count();

        return [
            'range' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'kpis' => [
                'total_users' => User::count(),
                'total_vendors' => User::query()->whereHas('roles', fn (Builder $q) => $q->where('slug', 'vendor'))->count(),
                'total_orders' => Order::count(),
                'active_farmers' => User::query()->whereHas('roles', fn (Builder $q) => $q->where('slug', 'farmer'))->count(),
                'revenue_current' => $revenueCurrent,
                'revenue_previous' => $revenuePrevious,
                'orders_current' => $ordersCurrent,
                'orders_previous' => $ordersPrevious,
                'revenue_delta' => $this->delta($revenueCurrent, $revenuePrevious),
                'orders_delta' => $this->delta($ordersCurrent, $ordersPrevious),
            ],
            'charts' => [
                'revenue_over_time' => $this->seriesRevenueByDay($fromDate, $toDate),
                'orders_by_category' => $this->seriesOrdersByCategory($fromDate, $toDate),
                'user_growth' => $this->seriesUserGrowthMonthly(6),
            ],
            'tables' => [
                'recent_orders' => Order::query()
                    ->with('farmer', 'vendor.vendorProfile')
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn (Order $order) => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'farmer' => $order->farmer?->name,
                        'vendor' => $order->vendor?->vendorProfile?->business_name ?? $order->vendor?->name,
                        'status' => $order->status,
                        'total' => (float) $order->total_amount,
                        'created_at' => $order->created_at?->toDateTimeString(),
                    ]),
                'recent_payments' => Payment::query()
                    ->with('user')
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn (Payment $payment) => [
                        'id' => $payment->id,
                        'reference' => $payment->provider_reference,
                        'provider' => strtoupper($payment->provider),
                        'user' => $payment->user?->name,
                        'amount' => (float) $payment->amount,
                        'status' => $payment->status,
                        'created_at' => $payment->created_at?->toDateTimeString(),
                    ]),
            ],
            'quick_actions' => [
                'vendors_pending_approval' => User::query()
                    ->whereHas('vendorProfile', fn (Builder $q) => $q->where('verification_status', 'pending'))
                    ->count(),
                'products_pending_moderation' => Product::query()->where('is_active', false)->count(),
            ],
        ];
    }

    public function vendor(User $vendor, ?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate] = $this->resolveRange($from, $to);

        $ordersQuery = Order::query()->where('vendor_id', $vendor->id);
        $rangeOrdersQuery = (clone $ordersQuery)->whereBetween('created_at', [$fromDate, $toDate]);

        $totalOrders = $rangeOrdersQuery->count();
        $paidOrders = (clone $rangeOrdersQuery)->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])->count();
        $conversionRate = $totalOrders > 0 ? round(($paidOrders / $totalOrders) * 100, 1) : 0.0;

        return [
            'range' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'kpis' => [
                'total_sales' => (float) (clone $rangeOrdersQuery)->sum('total_amount'),
                'orders_count' => $totalOrders,
                'revenue' => (float) (clone $rangeOrdersQuery)->sum('total_amount'),
                'conversion_rate' => $conversionRate,
                'active_products' => Product::query()->where('vendor_id', $vendor->id)->where('is_active', true)->count(),
                'low_stock_count' => Product::query()->where('vendor_id', $vendor->id)->where('inventory', '<=', 10)->count(),
            ],
            'charts' => [
                'sales_trend' => $this->seriesVendorSalesByDay($vendor->id, $fromDate, $toDate),
                'top_products' => OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('orders.vendor_id', $vendor->id)
                    ->whereBetween('orders.created_at', [$fromDate, $toDate])
                    ->select('products.name')
                    ->selectRaw('SUM(order_items.quantity) as quantity')
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('quantity')
                    ->limit(6)
                    ->get()
                    ->map(fn ($row) => ['label' => $row->name, 'value' => (int) $row->quantity]),
            ],
            'tables' => [
                'incoming_orders' => Order::query()
                    ->where('vendor_id', $vendor->id)
                    ->with('farmer')
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn (Order $order) => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'farmer' => $order->farmer?->name,
                        'status' => $order->status,
                        'total' => (float) $order->total_amount,
                        'created_at' => $order->created_at?->toDateTimeString(),
                    ]),
                'reviews' => Review::query()
                    ->whereHas('product', fn (Builder $q) => $q->where('vendor_id', $vendor->id))
                    ->with('product', 'user')
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn (Review $review) => [
                        'product' => $review->product?->name,
                        'user' => $review->user?->name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                    ]),
            ],
        ];
    }

    public function farmer(User $farmer, ?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate] = $this->resolveRange($from, $to);

        $cropTypes = collect($farmer->farmerProfile?->crop_types ?? [])->filter()->values();
        $region = $farmer->farmerProfile?->region;

        $recommendedProducts = Product::query()
            ->where('is_active', true)
            ->when($cropTypes->isNotEmpty(), fn (Builder $q) => $q->whereIn('crop_type', $cropTypes->all()))
            ->when($region, fn (Builder $q) => $q->where(fn (Builder $inner) => $inner->whereNull('region')->orWhere('region', $region)))
            ->latest()
            ->limit(8)
            ->get();

        return [
            'range' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'kpis' => [
                'orders_count' => Order::query()->where('farmer_id', $farmer->id)->count(),
                'orders_in_progress' => Order::query()->where('farmer_id', $farmer->id)->whereIn('status', ['pending', 'paid', 'processing', 'shipped'])->count(),
                'wishlist_items' => Wishlist::query()->where('user_id', $farmer->id)->count(),
                'bookings_count' => Booking::query()->where('farmer_id', $farmer->id)->count(),
            ],
            'charts' => [
                'orders_timeline' => Order::query()
                    ->selectRaw('DATE(created_at) as day')
                    ->selectRaw('COUNT(*) as total')
                    ->where('farmer_id', $farmer->id)
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->map(fn ($row) => [
                        'label' => Carbon::parse($row->day)->format('M d'),
                        'value' => (int) $row->total,
                    ]),
            ],
            'tables' => [
                'recommended_products' => $recommendedProducts->map(fn (Product $product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'price' => (float) $product->price,
                    'crop_type' => $product->crop_type,
                ]),
                'weather_updates' => WeatherInsight::query()
                    ->when($region, fn (Builder $q) => $q->where('region', $region))
                    ->orderByDesc('weather_date')
                    ->limit(5)
                    ->get()
                    ->map(fn (WeatherInsight $weather) => [
                        'date' => $weather->weather_date,
                        'summary' => $weather->summary,
                        'rainfall_probability' => $weather->rainfall_probability,
                        'alert_level' => $weather->alert_level,
                    ]),
                'knowledge_feed' => Article::query()
                    ->where('is_published', true)
                    ->latest('published_at')
                    ->limit(6)
                    ->get()
                    ->map(fn (Article $article) => [
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'excerpt' => $article->excerpt,
                    ]),
                'bookings' => Booking::query()
                    ->with('agronomist')
                    ->where('farmer_id', $farmer->id)
                    ->latest()
                    ->limit(8)
                    ->get()
                    ->map(fn (Booking $booking) => [
                        'topic' => $booking->topic,
                        'agronomist' => $booking->agronomist?->name,
                        'status' => $booking->status,
                        'scheduled_for' => $booking->scheduled_for?->toDateTimeString(),
                    ]),
                'suggested_experts' => User::query()
                    ->with('agronomistProfile')
                    ->whereHas('roles', fn (Builder $query) => $query->where('slug', 'agronomist'))
                    ->when($region, fn (Builder $query) => $query->whereHas('agronomistProfile', fn (Builder $inner) => $inner->whereJsonContains('regions_served', $region)))
                    ->limit(6)
                    ->get()
                    ->map(fn (User $expert) => [
                        'id' => $expert->id,
                        'name' => $expert->name,
                        'specialty' => $expert->agronomistProfile?->specialty,
                        'rate' => (float) ($expert->agronomistProfile?->hourly_rate ?? 0),
                    ]),
            ],
        ];
    }

    public function expert(User $expert, ?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate] = $this->resolveRange($from, $to);

        $sessionsQuery = Booking::query()->where('agronomist_id', $expert->id);
        $rangeSessions = (clone $sessionsQuery)->whereBetween('created_at', [$fromDate, $toDate]);

        $totalSessions = $rangeSessions->count();
        $completedSessions = (clone $rangeSessions)->where('status', 'completed')->count();
        $serviceScore = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 5, 2) : 0;

        return [
            'range' => [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
            ],
            'kpis' => [
                'total_sessions' => $totalSessions,
                'earnings' => (float) (clone $rangeSessions)->where('status', 'completed')->sum('amount'),
                'service_score' => $serviceScore,
                'availability' => (bool) ($expert->agronomistProfile?->is_available ?? false),
            ],
            'charts' => [
                'sessions_by_status' => collect(['pending', 'confirmed', 'completed', 'cancelled'])
                    ->map(fn (string $status) => [
                        'label' => ucfirst($status),
                        'value' => (int) (clone $sessionsQuery)->where('status', $status)->count(),
                    ]),
                'earnings_trend' => Booking::query()
                    ->selectRaw('DATE(created_at) as day')
                    ->selectRaw('SUM(amount) as total')
                    ->where('agronomist_id', $expert->id)
                    ->where('status', 'completed')
                    ->whereBetween('created_at', [$fromDate, $toDate])
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->map(fn ($row) => [
                        'label' => Carbon::parse($row->day)->format('M d'),
                        'value' => (float) $row->total,
                    ]),
            ],
            'tables' => [
                'upcoming_bookings' => Booking::query()
                    ->with('farmer')
                    ->where('agronomist_id', $expert->id)
                    ->where('scheduled_for', '>=', now())
                    ->orderBy('scheduled_for')
                    ->limit(10)
                    ->get()
                    ->map(fn (Booking $booking) => [
                        'id' => $booking->id,
                        'topic' => $booking->topic,
                        'farmer' => $booking->farmer?->name,
                        'status' => $booking->status,
                        'scheduled_for' => $booking->scheduled_for?->toDateTimeString(),
                    ]),
                'session_history' => Booking::query()
                    ->with('farmer')
                    ->where('agronomist_id', $expert->id)
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn (Booking $booking) => [
                        'topic' => $booking->topic,
                        'farmer' => $booking->farmer?->name,
                        'amount' => (float) $booking->amount,
                        'status' => $booking->status,
                    ]),
            ],
        ];
    }

    public function notifications(User $user): Collection
    {
        return Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'read_at' => $notification->read_at?->toDateTimeString(),
                'created_at' => $notification->created_at?->diffForHumans(),
            ]);
    }

    public function globalSearch(User $user, string $query): array
    {
        if ($query === '') {
            return ['products' => [], 'users' => [], 'orders' => []];
        }

        $products = Product::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('sku', 'like', "%{$query}%")
            ->limit(6)
            ->get(['id', 'name', 'slug']);

        $users = User::query()
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->limit(6)
            ->get(['id', 'name', 'email']);

        $orders = Order::query()
            ->where('order_number', 'like', "%{$query}%")
            ->limit(6)
            ->get(['id', 'order_number', 'status', 'total_amount']);

        return [
            'products' => $products,
            'users' => $users,
            'orders' => $orders,
        ];
    }

    private function resolveRange(?string $from = null, ?string $to = null): array
    {
        $toDate = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : $toDate->copy()->subDays(6)->startOfDay();

        if ($fromDate->gt($toDate)) {
            [$fromDate, $toDate] = [$toDate->copy()->startOfDay(), $fromDate->copy()->endOfDay()];
        }

        $windowDays = max(1, $fromDate->diffInDays($toDate) + 1);
        $previousFrom = $fromDate->copy()->subDays($windowDays);
        $previousTo = $fromDate->copy()->subSecond();

        return [$fromDate, $toDate, $previousFrom, $previousTo];
    }

    private function delta(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) {
            return (float) $current > 0.0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    private function seriesRevenueByDay(Carbon $from, Carbon $to): Collection
    {
        return Order::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(total_amount) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::parse($row->day)->format('M d'),
                'value' => (float) $row->total,
            ]);
    }

    private function seriesOrdersByCategory(Carbon $from, Carbon $to): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->whereBetween('orders.created_at', [$from, $to])
            ->select('categories.name')
            ->selectRaw('COUNT(order_items.id) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->name,
                'value' => (int) $row->total,
            ]);
    }

    private function seriesUserGrowthMonthly(int $months = 6): Collection
    {
        $start = now()->copy()->startOfMonth()->subMonths($months - 1);

        return User::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->selectRaw('COUNT(*) as total')
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->orderBy('ym')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->ym)->format('M'),
                'value' => (int) $row->total,
            ]);
    }

    private function seriesVendorSalesByDay(int $vendorId, Carbon $from, Carbon $to): Collection
    {
        return Order::query()
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(total_amount) as total')
            ->where('vendor_id', $vendorId)
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::parse($row->day)->format('M d'),
                'value' => (float) $row->total,
            ]);
    }
}

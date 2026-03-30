<?php

namespace App\Services\Dashboard;

use App\Models\Article;
use App\Models\ArticleView;
use App\Models\AdminAlert;
use App\Models\AdminInsight;
use App\Models\AdminAuditLog;
use App\Models\Booking;
use App\Models\ExpertReview;
use App\Models\ForecastSnapshot;
use App\Models\InventoryFlag;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\WeatherInsight;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DashboardAnalyticsService
{
    public function admin(User $user, ?string $from = null, ?string $to = null): array
    {
        [$fromDate, $toDate, $previousFrom, $previousTo] = $this->resolveRange($from, $to);

        $revenueCurrent = (float) Order::query()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_amount');
        $revenuePrevious = (float) Order::query()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])
            ->whereBetween('created_at', [$previousFrom, $previousTo])
            ->sum('total_amount');
        $ordersCurrent = Order::query()->whereBetween('created_at', [$fromDate, $toDate])->count();
        $ordersPrevious = Order::query()->whereBetween('created_at', [$previousFrom, $previousTo])->count();
        $platformEarnings = (float) Order::query()->whereBetween('created_at', [$fromDate, $toDate])->sum('commission_amount');
        $vendorEarnings = max(0.0, $revenueCurrent - $platformEarnings);
        $paidOrdersInRange = Order::query()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])
            ->count();
        $conversionRate = $ordersCurrent > 0 ? round(($paidOrdersInRange / $ordersCurrent) * 100, 1) : 0.0;
        $aov = $paidOrdersInRange > 0 ? round($revenueCurrent / $paidOrdersInRange, 2) : 0.0;
        $monthlyRecurringRevenue = (float) Order::query()
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered', 'completed'])
            ->where('created_at', '>=', now()->copy()->subDays(30))
            ->sum('total_amount');

        $dau = $this->dailyActiveUsers();
        $retentionRate = $this->customerRetentionRate();

        $automationRules = Setting::query()
            ->where('group', 'automation_rules')
            ->pluck('value', 'key')
            ->toArray();
        $reportScheduleRaw = Setting::query()
            ->where('group', 'reporting')
            ->where('key', 'schedule')
            ->value('value');
        $reportSchedule = $reportScheduleRaw ? (json_decode((string) $reportScheduleRaw, true) ?: []) : [];
        $hasPayments = Schema::hasTable('payments');

        $stuckOrderHours = (int) ($automationRules['stuck_order_hours'] ?? 48);
        $unusualOrderMultiplier = (float) ($automationRules['unusual_order_multiplier'] ?? 2.5);
        $fraudGuardEnabled = ($automationRules['fraud_guard_enabled'] ?? '0') === '1';
        $stuckOrderAlertEnabled = ($automationRules['stuck_order_alert'] ?? '0') === '1';

        $failedPayments24h = $hasPayments ? Payment::query()
            ->where('created_at', '>=', now()->subDay())
            ->whereIn('status', ['failed', 'error'])
            ->count() : 0;

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
                'total_revenue' => $revenueCurrent,
                'revenue_previous' => $revenuePrevious,
                'mrr' => $monthlyRecurringRevenue,
                'dau' => $dau,
                'conversion_rate' => $conversionRate,
                'aov' => $aov,
                'orders_current' => $ordersCurrent,
                'orders_previous' => $ordersPrevious,
                'revenue_delta' => $this->delta($revenueCurrent, $revenuePrevious),
                'orders_delta' => $this->delta($ordersCurrent, $ordersPrevious),
                'platform_earnings' => $platformEarnings,
                'vendor_earnings' => $vendorEarnings,
                'retention_rate' => $retentionRate,
            ],
            'charts' => [
                'revenue_over_time' => $this->seriesRevenueByDay($fromDate, $toDate),
                'orders_by_category' => $this->seriesOrdersByCategory($fromDate, $toDate),
                'user_growth' => $this->seriesUserGrowthMonthly(6),
                'users_active_vs_inactive' => [
                    ['label' => 'Active', 'value' => User::query()->where('status', 'active')->count()],
                    ['label' => 'Inactive', 'value' => User::query()->whereIn('status', ['inactive', 'suspended'])->count()],
                ],
            ],
            'analytics' => [
                'top_selling_products' => OrderItem::query()
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->whereBetween('orders.created_at', [$fromDate, $toDate])
                    ->select('products.id', 'products.name')
                    ->selectRaw('SUM(order_items.quantity) as units_sold')
                    ->selectRaw('SUM(order_items.line_total) as revenue')
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('units_sold')
                    ->limit(8)
                    ->get(),
                'vendor_performance' => Order::query()
                    ->join('users as vendors', 'vendors.id', '=', 'orders.vendor_id')
                    ->leftJoin('vendor_profiles', 'vendor_profiles.user_id', '=', 'vendors.id')
                    ->whereBetween('orders.created_at', [$fromDate, $toDate])
                    ->selectRaw('orders.vendor_id')
                    ->selectRaw("COALESCE(vendor_profiles.business_name, vendors.name) as vendor_name")
                    ->selectRaw('COUNT(orders.id) as total_orders')
                    ->selectRaw('SUM(orders.total_amount) as gross_revenue')
                    ->selectRaw('SUM(orders.commission_amount) as commission')
                    ->groupBy('orders.vendor_id', 'vendor_profiles.business_name', 'vendors.name')
                    ->orderByDesc('gross_revenue')
                    ->limit(10)
                    ->get(),
                'category_performance' => $this->seriesOrdersByCategory($fromDate, $toDate),
            ],
            'operations' => [
                'order_stream' => Order::query()
                    ->with('farmer:id,name', 'vendor:id,name', 'vendor.vendorProfile:id,user_id,business_name')
                    ->latest()
                    ->limit(12)
                    ->get(),
                'stuck_orders' => Order::query()
                    ->with('vendor.vendorProfile')
                    ->whereIn('status', ['pending', 'paid', 'processing'])
                    ->where('updated_at', '<=', now()->subHours($stuckOrderHours))
                    ->latest('updated_at')
                    ->limit(10)
                    ->get(),
                'pending_vendor_applications' => VendorProfile::query()
                    ->with('user')
                    ->where('verification_status', 'pending')
                    ->latest()
                    ->limit(10)
                    ->get(),
            ],
            'finance' => [
                'ledger' => $hasPayments ? Payment::query()
                    ->with('user:id,name,email')
                    ->latest()
                    ->limit(15)
                    ->get() : collect(),
                'vendor_payout_candidates' => Order::query()
                    ->join('users as vendors', 'vendors.id', '=', 'orders.vendor_id')
                    ->leftJoin('vendor_profiles', 'vendor_profiles.user_id', '=', 'vendors.id')
                    ->whereBetween('orders.created_at', [$fromDate, $toDate])
                    ->whereIn('orders.status', ['delivered', 'completed'])
                    ->selectRaw('orders.vendor_id')
                    ->selectRaw("COALESCE(vendor_profiles.business_name, vendors.name) as vendor_name")
                    ->selectRaw('SUM(orders.total_amount - orders.commission_amount) as payout_amount')
                    ->groupBy('orders.vendor_id', 'vendor_profiles.business_name', 'vendors.name')
                    ->orderByDesc('payout_amount')
                    ->limit(10)
                    ->get(),
            ],
            'advisory' => [
                'sessions_completed' => Booking::query()->where('status', 'completed')->count(),
                'sessions_pending' => Booking::query()->where('status', 'pending')->count(),
                'average_rating' => round((float) ExpertReview::query()->avg('rating'), 2),
                'revenue_per_expert' => Booking::query()
                    ->join('users as experts', 'experts.id', '=', 'bookings.agronomist_id')
                    ->leftJoin('agronomist_profiles', 'agronomist_profiles.user_id', '=', 'experts.id')
                    ->whereBetween('bookings.created_at', [$fromDate, $toDate])
                    ->selectRaw('bookings.agronomist_id as expert_id')
                    ->selectRaw("COALESCE(agronomist_profiles.specialty, experts.name) as expert_name")
                    ->selectRaw('COUNT(bookings.id) as sessions')
                    ->selectRaw('SUM(bookings.amount) as revenue')
                    ->groupBy('bookings.agronomist_id', 'agronomist_profiles.specialty', 'experts.name')
                    ->orderByDesc('revenue')
                    ->limit(8)
                    ->get(),
            ],
            'content' => [
                'most_viewed_articles' => ArticleView::query()
                    ->join('articles', 'articles.id', '=', 'article_views.article_id')
                    ->select('articles.id', 'articles.title', 'articles.slug')
                    ->selectRaw('COUNT(article_views.id) as views')
                    ->groupBy('articles.id', 'articles.title', 'articles.slug')
                    ->orderByDesc('views')
                    ->limit(8)
                    ->get(),
                'recent_articles' => Article::query()
                    ->with('author:id,name')
                    ->latest()
                    ->limit(8)
                    ->get(),
            ],
            'notifications' => [
                'recent' => Notification::query()
                    ->with('user:id,name')
                    ->latest()
                    ->limit(12)
                    ->get(),
                'unread_count' => Notification::query()->whereNull('read_at')->count(),
            ],
            'monitoring' => [
                'log_tail' => $this->tailLaravelErrors(25),
                'system_health' => [
                    'failed_payments_24h' => $hasPayments ? Payment::query()
                        ->where('created_at', '>=', now()->subDay())
                        ->whereIn('status', ['failed', 'error'])
                        ->count() : 0,
                    'stuck_orders' => Order::query()
                        ->whereIn('status', ['pending', 'paid', 'processing'])
                        ->where('updated_at', '<=', now()->subHours($stuckOrderHours))
                        ->count(),
                    'pending_vendor_kyc' => VendorProfile::query()->where('verification_status', 'pending')->count(),
                ],
            ],
            'audit_logs' => AdminAuditLog::query()
                ->with('user:id,name,email')
                ->latest('created_at')
                ->limit(12)
                ->get(),
            'risk_flags' => [
                'enabled' => [
                    'fraud_guard' => $fraudGuardEnabled,
                    'stuck_order_alert' => $stuckOrderAlertEnabled,
                ],
                'unusual_vendor_volume' => $this->unusualVendorOrderVolume($fromDate, $toDate, $unusualOrderMultiplier),
                'suspicious_payments' => $hasPayments ? Payment::query()
                    ->with('user:id,name,email')
                    ->whereIn('status', ['pending', 'failed'])
                    ->latest()
                    ->limit(10)
                    ->get() : collect(),
                'fake_vendor_signals' => VendorProfile::query()
                    ->with('user:id,name,email,created_at')
                    ->where('verification_status', 'pending')
                    ->where(function (Builder $query): void {
                        $query->whereNull('business_type')
                            ->orWhereNull('description')
                            ->orWhere('created_at', '<=', now()->subDays(14));
                    })
                    ->latest()
                    ->limit(10)
                    ->get(),
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
                'recent_payments' => $hasPayments
                    ? Payment::query()
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
                        ])
                    : collect(),
            ],
            'quick_actions' => [
                'vendors_pending_approval' => User::query()
                    ->whereHas('vendorProfile', fn (Builder $q) => $q->where('verification_status', 'pending'))
                    ->count(),
                'products_pending_moderation' => Product::query()->where('is_active', false)->count(),
            ],
            'automation' => [
                'stuck_order_alert' => $stuckOrderAlertEnabled,
                'fraud_guard_enabled' => $fraudGuardEnabled,
                'weekly_report_enabled' => ($automationRules['weekly_report_enabled'] ?? '0') === '1',
                'auto_suspend_vendor_enabled' => ($automationRules['auto_suspend_vendor_enabled'] ?? '0') === '1',
                'stuck_order_hours' => $stuckOrderHours,
                'unusual_order_multiplier' => $unusualOrderMultiplier,
            ],
            'reporting' => [
                'schedule' => [
                    'frequency' => $reportSchedule['frequency'] ?? 'weekly',
                    'delivery_channel' => $reportSchedule['delivery_channel'] ?? 'email',
                    'recipient_email' => $reportSchedule['recipient_email'] ?? null,
                    'enabled' => (bool) ($reportSchedule['enabled'] ?? false),
                    'updated_at' => $reportSchedule['updated_at'] ?? null,
                ],
            ],
            'insights' => $this->buildAdminInsights(
                revenueDelta: $this->delta($revenueCurrent, $revenuePrevious),
                ordersDelta: $this->delta($ordersCurrent, $ordersPrevious),
                conversionRate: $conversionRate,
                pendingVendorApprovals: User::query()
                    ->whereHas('vendorProfile', fn (Builder $q) => $q->where('verification_status', 'pending'))
                    ->count(),
                failedPayments24h: $failedPayments24h
            ),
            'ai_insights' => AdminInsight::query()
                ->latest('observed_at')
                ->limit(8)
                ->get(),
            'alerts' => AdminAlert::query()
                ->whereNull('resolved_at')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(),
            'inventory_flags' => InventoryFlag::query()
                ->with('product:id,name', 'vendor:id,name')
                ->whereNull('resolved_at')
                ->orderByDesc('detected_at')
                ->limit(10)
                ->get(),
            'forecasts' => [
                'revenue' => ForecastSnapshot::query()
                    ->where('type', 'revenue')
                    ->latest('generated_at')
                    ->first(),
                'user_growth' => ForecastSnapshot::query()
                    ->where('type', 'user_growth')
                    ->latest('generated_at')
                    ->first(),
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

    private function dailyActiveUsers(): int
    {
        $start = now()->startOfDay();
        $end = now()->endOfDay();

        $orderUsers = Order::query()->whereBetween('created_at', [$start, $end])->pluck('farmer_id');
        $bookingUsers = Booking::query()
            ->whereBetween('created_at', [$start, $end])
            ->pluck('farmer_id')
            ->merge(Booking::query()->whereBetween('created_at', [$start, $end])->pluck('agronomist_id'));
        $contentUsers = ArticleView::query()->whereBetween('created_at', [$start, $end])->whereNotNull('user_id')->pluck('user_id');

        return $orderUsers->merge($bookingUsers)->merge($contentUsers)->filter()->unique()->count();
    }

    private function customerRetentionRate(): float
    {
        $buyersWithAnyOrder = Order::query()
            ->select('farmer_id')
            ->groupBy('farmer_id')
            ->get()
            ->count();

        if ($buyersWithAnyOrder === 0) {
            return 0.0;
        }

        $repeatBuyers = Order::query()
            ->select('farmer_id')
            ->groupBy('farmer_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();

        return round(($repeatBuyers / $buyersWithAnyOrder) * 100, 1);
    }

    private function unusualVendorOrderVolume(Carbon $fromDate, Carbon $toDate, float $multiplier): Collection
    {
        $baseline = Order::query()
            ->select('vendor_id')
            ->selectRaw('COUNT(*) as order_count')
            ->groupBy('vendor_id')
            ->pluck('order_count');

        $average = max(1.0, (float) $baseline->avg());
        $threshold = max(5, (int) ceil($average * $multiplier));

        return Order::query()
            ->join('users as vendors', 'vendors.id', '=', 'orders.vendor_id')
            ->leftJoin('vendor_profiles', 'vendor_profiles.user_id', '=', 'vendors.id')
            ->whereBetween('orders.created_at', [$fromDate, $toDate])
            ->selectRaw('orders.vendor_id')
            ->selectRaw("COALESCE(vendor_profiles.business_name, vendors.name) as vendor_name")
            ->selectRaw('COUNT(orders.id) as order_count')
            ->groupBy('orders.vendor_id', 'vendor_profiles.business_name', 'vendors.name')
            ->havingRaw('COUNT(orders.id) >= ?', [$threshold])
            ->orderByDesc('order_count')
            ->limit(10)
            ->get();
    }

    private function tailLaravelErrors(int $lineCount = 25): array
    {
        $path = storage_path('logs/laravel.log');
        if (! File::exists($path)) {
            return [];
        }

        $lines = collect(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [])
            ->filter(fn (string $line) => str_contains($line, 'ERROR') || str_contains($line, 'CRITICAL'))
            ->take(-$lineCount)
            ->values()
            ->all();

        return $lines;
    }

    private function buildAdminInsights(
        float $revenueDelta,
        float $ordersDelta,
        float $conversionRate,
        int $pendingVendorApprovals,
        int $failedPayments24h
    ): array {
        $insights = [];

        if ($revenueDelta < 0) {
            $insights[] = [
                'severity' => 'high',
                'title' => 'Revenue trend is down',
                'message' => "Revenue is {$revenueDelta}% vs previous period. Prioritize category-level drill-down and vendor outreach.",
            ];
        } else {
            $insights[] = [
                'severity' => 'positive',
                'title' => 'Revenue trend is healthy',
                'message' => "Revenue is up {$revenueDelta}% vs previous period.",
            ];
        }

        if ($conversionRate < 35) {
            $insights[] = [
                'severity' => 'medium',
                'title' => 'Checkout conversion opportunity',
                'message' => "Conversion is {$conversionRate}%. Review failed payments and stuck orders to recover drop-offs.",
            ];
        }

        if ($pendingVendorApprovals > 0) {
            $insights[] = [
                'severity' => 'medium',
                'title' => 'Vendor approvals pending',
                'message' => "{$pendingVendorApprovals} vendor applications are waiting; clearing backlog can increase catalog depth.",
            ];
        }

        if ($failedPayments24h > 0) {
            $insights[] = [
                'severity' => 'high',
                'title' => 'Payment failures detected',
                'message' => "{$failedPayments24h} failed payments in the last 24 hours need provider-level review.",
            ];
        }

        if ($ordersDelta > 0 && $revenueDelta <= 0) {
            $insights[] = [
                'severity' => 'medium',
                'title' => 'Order count up but revenue flat/down',
                'message' => 'Average order value may be under pressure. Consider bundle promotions and cross-sell blocks.',
            ];
        }

        return $insights;
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

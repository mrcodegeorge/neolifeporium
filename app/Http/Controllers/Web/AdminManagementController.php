<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\AgronomistProfile;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\Auth\RoleOnboardingService;
use App\Services\Admin\AdminAlertService;
use App\Services\Admin\AdminInsightService;
use App\Services\Admin\ForecastService;
use App\Services\Admin\InventoryIntelligenceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class AdminManagementController extends Controller
{
    public function __construct(private readonly RoleOnboardingService $roleOnboarding) {}

    public function users(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString();
        $role = $request->string('role')->toString();

        $users = User::query()
            ->with('roles', 'vendorProfile')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($role !== '', function (Builder $query) use ($role): void {
                $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('slug', $role));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users', [
            'users' => $users,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'role' => $role,
            ],
        ]);
    }

    public function vendors(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('verification_status')->toString();
        $region = $request->string('region')->toString();

        $vendors = VendorProfile::query()
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('business_name', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('verification_status', $status))
            ->when($region !== '', fn (Builder $query) => $query->where('region', $region))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $regions = VendorProfile::query()
            ->whereNotNull('region')
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        return view('admin.vendors', [
            'vendors' => $vendors,
            'filters' => [
                'search' => $search,
                'verification_status' => $status,
                'region' => $region,
            ],
            'regions' => $regions,
        ]);
    }

    public function products(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('status')->toString();
        $featured = $request->string('featured')->toString();

        $products = Product::query()
            ->with('vendor.vendorProfile', 'category')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('is_active', $status === 'active'))
            ->when($featured !== '', fn (Builder $query) => $query->where('is_featured', $featured === 'featured'))
            ->latest()
            ->paginate(20)
            ->withQueryString();
        $categories = \App\Models\Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products', [
            'products' => $products,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'featured' => $featured,
            ],
        ]);
    }

    public function experts(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = $request->string('verification_status')->toString();

        $experts = AgronomistProfile::query()
            ->with('user')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('specialty', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status !== '', fn (Builder $query) => $query->where('verification_status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.experts', [
            'experts' => $experts,
            'filters' => [
                'search' => $search,
                'verification_status' => $status,
            ],
        ]);
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        $user->update(['status' => $payload['status']]);

        return back()->with('status', 'User status updated.');
    }

    public function updateVendorStatus(Request $request, VendorProfile $vendorProfile): RedirectResponse
    {
        $payload = $request->validate([
            'verification_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $vendorProfile->update([
            'verification_status' => $payload['verification_status'],
            'verified_at' => $payload['verification_status'] === 'approved' ? now() : null,
        ]);

        $this->roleOnboarding->syncApproval(
            $vendorProfile->user,
            RoleType::Vendor->value,
            $payload['verification_status']
        );

        return back()->with('status', 'Vendor verification updated.');
    }

    public function updateExpertStatus(Request $request, AgronomistProfile $agronomistProfile): RedirectResponse
    {
        $payload = $request->validate([
            'verification_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $agronomistProfile->update([
            'verification_status' => $payload['verification_status'],
            'verified_at' => $payload['verification_status'] === 'approved' ? now() : null,
        ]);

        $this->roleOnboarding->syncApproval(
            $agronomistProfile->user,
            RoleType::Agronomist->value,
            $payload['verification_status']
        );

        return back()->with('status', 'Expert verification updated.');
    }

    public function moderateProduct(Request $request, Product $product): RedirectResponse
    {
        $payload = $request->validate(['is_active' => ['required', 'boolean']]);
        $product->update(['is_active' => $payload['is_active']]);

        return back()->with('status', 'Product moderation updated.');
    }

    public function updateProductInputs(Request $request, Product $product): RedirectResponse
    {
        $payload = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'sku' => ['required', 'string', 'max:120', Rule::unique('products', 'sku')->ignore($product->id)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'product_type' => ['required', 'in:physical,service,digital'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'inventory' => ['required', 'integer', 'min:0'],
            'crop_type' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
        ]);

        $slug = Str::slug($payload['name']);
        $originalSlug = $slug;
        $suffix = 1;
        while (Product::query()->where('slug', $slug)->whereKeyNot($product->id)->exists()) {
            $slug = "{$originalSlug}-{$suffix}";
            $suffix++;
        }

        $product->update([
            ...$payload,
            'slug' => $slug,
        ]);

        return back()->with('status', 'Product details updated successfully.');
    }

    public function toggleFeaturedProduct(Product $product): RedirectResponse
    {
        $product->update(['is_featured' => ! $product->is_featured]);

        return back()->with('status', 'Product featured flag updated.');
    }

    public function markPaymentVerified(Payment $payment): RedirectResponse
    {
        $payment->update([
            'status' => 'success',
            'verified_at' => now(),
        ]);

        return back()->with('status', 'Payment marked as verified.');
    }

    public function updateBookingStatus(Request $request, Booking $booking): RedirectResponse
    {
        $payload = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $booking->update([
            'status' => $payload['status'],
        ]);

        return back()->with('status', 'Booking status updated.');
    }

    public function exportOrdersCsv(Request $request): StreamedResponse
    {
        $to = $request->filled('to') ? Carbon::parse((string) $request->string('to'))->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse((string) $request->string('from'))->startOfDay() : $to->copy()->subDays(6)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $orders = Order::query()
            ->with('farmer', 'vendor.vendorProfile')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'orders_export_'.$from->format('Ymd').'_to_'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Order Number',
                'Farmer',
                'Vendor',
                'Status',
                'Subtotal',
                'Commission',
                'Total',
                'Currency',
                'Created At',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->farmer?->name,
                    $order->vendor?->vendorProfile?->business_name ?? $order->vendor?->name,
                    $order->status,
                    $order->subtotal,
                    $order->commission_amount,
                    $order->total_amount,
                    $order->currency,
                    $order->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportUsersCsv(Request $request): StreamedResponse
    {
        $to = $request->filled('to') ? Carbon::parse((string) $request->string('to'))->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse((string) $request->string('from'))->startOfDay() : $to->copy()->subDays(30)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $users = User::query()
            ->with('roles')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $filename = 'users_export_'.$from->format('Ymd').'_to_'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($users): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Status', 'Roles', 'Created At']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->status,
                    $user->roles->pluck('slug')->implode('|'),
                    $user->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPaymentsCsv(Request $request): StreamedResponse
    {
        $to = $request->filled('to') ? Carbon::parse((string) $request->string('to'))->endOfDay() : now()->endOfDay();
        $from = $request->filled('from') ? Carbon::parse((string) $request->string('from'))->startOfDay() : $to->copy()->subDays(30)->startOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $payments = Payment::query()
            ->with('user')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $filename = 'payments_export_'.$from->format('Ymd').'_to_'.$to->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($payments): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Reference', 'User', 'Provider', 'Amount', 'Status', 'Verified At', 'Created At']);

            foreach ($payments as $payment) {
                fputcsv($handle, [
                    $payment->provider_reference,
                    $payment->user?->name,
                    strtoupper((string) $payment->provider),
                    $payment->amount,
                    $payment->status,
                    $payment->verified_at?->format('Y-m-d H:i:s'),
                    $payment->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function impersonate(User $user): RedirectResponse
    {
        abort_if((int) auth()->id() === (int) $user->id, 422, 'You are already this user.');

        session([
            'impersonator_id' => auth()->id(),
            'impersonator_guard' => 'web',
        ]);

        Auth::login($user);
        session(['active_role' => $user->roles()->value('slug')]);

        return redirect()->route('dashboard.redirect')->with('status', "Impersonating {$user->name}.");
    }

    public function leaveImpersonation(): RedirectResponse
    {
        $adminId = (int) session('impersonator_id');
        abort_if($adminId <= 0, 403, 'No impersonation session found.');

        $admin = User::query()->findOrFail($adminId);
        Auth::login($admin);
        session()->forget(['impersonator_id', 'impersonator_guard']);
        session(['active_role' => $admin->roles()->value('slug')]);

        return redirect()->route('admin.panel')->with('status', 'Returned to admin session.');
    }

    public function broadcastNotification(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:1000'],
            'role' => ['nullable', 'string', 'in:farmer,vendor,agronomist,admin,super_admin'],
            'region' => ['nullable', 'string', 'max:120'],
            'channel' => ['nullable', 'string', 'in:in_app,email,sms'],
        ]);

        $channel = $payload['channel'] ?? 'in_app';

        User::query()
            ->when(! empty($payload['role']), function (Builder $query) use ($payload): void {
                $query->whereHas('roles', fn (Builder $roleQuery) => $roleQuery->where('slug', $payload['role']));
            })
            ->when(! empty($payload['region']), function (Builder $query) use ($payload): void {
                $region = $payload['region'];
                $query->where(function (Builder $inner) use ($region): void {
                    $inner->whereHas('farmerProfile', fn (Builder $farmerQuery) => $farmerQuery->where('region', $region))
                        ->orWhereHas('vendorProfile', fn (Builder $vendorQuery) => $vendorQuery->where('region', $region))
                        ->orWhereHas('agronomistProfile', fn (Builder $expertQuery) => $expertQuery->whereJsonContains('regions_served', $region));
                });
            })
            ->select('id')
            ->chunkById(500, function ($users) use ($payload, $channel): void {
                $rows = $users->map(fn (User $user) => [
                    'user_id' => $user->id,
                    'type' => 'admin_broadcast',
                    'channel' => $channel,
                    'title' => $payload['title'],
                    'message' => $payload['message'],
                    'payload' => json_encode([
                        'role_filter' => $payload['role'] ?? null,
                        'region_filter' => $payload['region'] ?? null,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all();

                if ($rows !== []) {
                    Notification::query()->insert($rows);
                }
            });

        return back()->with('status', 'Broadcast notification queued to target audience.');
    }

    public function updateAutomationRules(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'stuck_order_alert' => ['nullable', 'boolean'],
            'fraud_guard_enabled' => ['nullable', 'boolean'],
            'weekly_report_enabled' => ['nullable', 'boolean'],
            'auto_suspend_vendor_enabled' => ['nullable', 'boolean'],
            'stuck_order_hours' => ['nullable', 'integer', 'min:1', 'max:240'],
            'unusual_order_multiplier' => ['nullable', 'numeric', 'min:1.2', 'max:10'],
        ]);

        $defaults = [
            'stuck_order_alert' => false,
            'fraud_guard_enabled' => false,
            'weekly_report_enabled' => false,
            'auto_suspend_vendor_enabled' => false,
            'stuck_order_hours' => 48,
            'unusual_order_multiplier' => 2.5,
        ];

        $rules = array_merge($defaults, $payload);

        foreach ($rules as $key => $value) {
            Setting::query()->updateOrCreate(
                ['group' => 'automation_rules', 'key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value]
            );
        }

        return back()->with('status', 'Automation rules updated.');
    }

    public function updateVendorInputs(Request $request, VendorProfile $vendorProfile): RedirectResponse
    {
        $payload = $request->validate([
            'business_name' => ['required', 'string', 'max:190'],
            'business_type' => ['nullable', 'string', 'max:120'],
            'product_category' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'region' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'commission_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $vendorProfile->update($payload);

        return back()->with('status', 'Vendor profile inputs updated.');
    }

    public function updateExpertInputs(Request $request, AgronomistProfile $agronomistProfile): RedirectResponse
    {
        $payload = $request->validate([
            'specialty' => ['required', 'string', 'max:150'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'regions_served_text' => ['nullable', 'string', 'max:500'],
            'is_available' => ['nullable', 'boolean'],
        ]);

        $regions = collect(explode(',', (string) ($payload['regions_served_text'] ?? '')))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        $agronomistProfile->update([
            'specialty' => $payload['specialty'],
            'experience_years' => $payload['experience_years'] ?? null,
            'bio' => $payload['bio'] ?? null,
            'hourly_rate' => $payload['hourly_rate'],
            'regions_served' => $regions,
            'is_available' => (bool) ($payload['is_available'] ?? false),
        ]);

        return back()->with('status', 'Expert profile inputs updated.');
    }

    public function updateReportSchedule(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'frequency' => ['required', 'string', 'in:weekly,monthly'],
            'delivery_channel' => ['required', 'string', 'in:email,in_app'],
            'recipient_email' => ['nullable', 'email', 'max:190'],
            'enabled' => ['nullable', 'boolean'],
        ]);

        $enabled = (bool) ($payload['enabled'] ?? false);

        $config = [
            'frequency' => $payload['frequency'],
            'delivery_channel' => $payload['delivery_channel'],
            'recipient_email' => $payload['recipient_email'] ?? null,
            'enabled' => $enabled,
            'updated_by' => auth()->id(),
            'updated_at' => now()->toDateTimeString(),
        ];

        Setting::query()->updateOrCreate(
            ['group' => 'reporting', 'key' => 'schedule'],
            ['value' => json_encode($config)]
        );

        return back()->with('status', 'Report schedule updated.');
    }

    public function runIntelligenceNow(
        AdminInsightService $insightService,
        ForecastService $forecastService,
        AdminAlertService $alertService,
        InventoryIntelligenceService $inventoryService
    ): RedirectResponse {
        $insights = $insightService->generate();
        $insightService->persist($insights);

        $forecastService->persist('revenue', $forecastService->generateRevenueForecast());
        $forecastService->persist('user_growth', $forecastService->generateUserGrowthForecast());

        $alertService->run();
        $inventoryService->run();

        return back()->with('status', 'Intelligence engines executed successfully.');
    }
}

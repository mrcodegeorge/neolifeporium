<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\AgronomistProfile;
use App\Models\Booking;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\Auth\RoleOnboardingService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return view('admin.products', [
            'products' => $products,
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
}

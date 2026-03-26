<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Dashboard\DashboardAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardDataController extends Controller
{
    public function __construct(private readonly DashboardAnalyticsService $analytics) {}

    public function farmer(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->analytics->farmer(
                $request->user(),
                $request->string('from')->toString() ?: null,
                $request->string('to')->toString() ?: null
            ),
        ]);
    }

    public function vendor(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->analytics->vendor(
                $request->user(),
                $request->string('from')->toString() ?: null,
                $request->string('to')->toString() ?: null
            ),
        ]);
    }

    public function expert(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->analytics->expert(
                $request->user(),
                $request->string('from')->toString() ?: null,
                $request->string('to')->toString() ?: null
            ),
        ]);
    }

    public function admin(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->analytics->admin(
                $request->user(),
                $request->string('from')->toString() ?: null,
                $request->string('to')->toString() ?: null
            ),
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->analytics->notifications($request->user()),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->string('q'));

        return response()->json([
            'success' => true,
            'data' => $this->analytics->globalSearch($request->user(), $query),
        ]);
    }

    public function vendorUpdateOrderStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,paid,processing,shipped,delivered,completed'],
        ]);

        abort_unless(
            $request->user()?->hasAnyRole([RoleType::Vendor->value, RoleType::Admin->value, RoleType::SuperAdmin->value]),
            403
        );
        abort_unless($order->vendor_id === $request->user()?->id || $request->user()?->hasAnyRole([RoleType::Admin->value, RoleType::SuperAdmin->value]), 403);

        $order->update(['status' => $request->string('status')->toString()]);

        return response()->json(['success' => true, 'message' => 'Order status updated.']);
    }

    public function expertUpdateBookingStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        abort_unless(
            $request->user()?->hasAnyRole([RoleType::Agronomist->value, RoleType::Admin->value, RoleType::SuperAdmin->value]),
            403
        );
        abort_unless($booking->agronomist_id === $request->user()?->id || $request->user()?->hasAnyRole([RoleType::Admin->value, RoleType::SuperAdmin->value]), 403);

        $booking->update(['status' => $request->string('status')->toString()]);

        return response()->json(['success' => true, 'message' => 'Booking status updated.']);
    }

    public function toggleExpertAvailability(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_unless($user?->hasAnyRole([RoleType::Agronomist->value, RoleType::Admin->value, RoleType::SuperAdmin->value]), 403);

        $profile = $user->agronomistProfile;
        abort_unless($profile !== null, 422, 'Agronomist profile is missing.');

        $profile->update(['is_available' => ! $profile->is_available]);

        return response()->json([
            'success' => true,
            'message' => 'Availability updated.',
            'data' => ['is_available' => (bool) $profile->fresh()->is_available],
        ]);
    }

    public function markNotificationRead(Request $request, Notification $notification): JsonResponse
    {
        abort_unless($notification->user_id === $request->user()?->id, 403);

        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function exportVendorOrdersCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $orders = Order::query()
            ->where('vendor_id', $request->user()?->id)
            ->latest()
            ->with('farmer')
            ->get();

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Number', 'Farmer', 'Status', 'Total', 'Date']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->farmer?->name,
                    $order->status,
                    $order->total_amount,
                    $order->created_at?->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, 'vendor_orders.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportFarmerOrdersCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $orders = Order::query()
            ->where('farmer_id', $request->user()?->id)
            ->latest()
            ->with('vendor.vendorProfile')
            ->get();

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Order Number', 'Vendor', 'Status', 'Total', 'Date']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->vendor?->vendorProfile?->business_name ?? $order->vendor?->name,
                    $order->status,
                    $order->total_amount,
                    $order->created_at?->toDateTimeString(),
                ]);
            }
            fclose($handle);
        }, 'farmer_orders.csv', ['Content-Type' => 'text/csv']);
    }
}

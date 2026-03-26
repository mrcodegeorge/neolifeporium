<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\AgronomistProfile;
use App\Models\Article;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Models\VendorProfile;
use App\Services\Auth\RoleOnboardingService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private readonly RoleOnboardingService $roleOnboarding) {}

    public function dashboard()
    {
        return ApiResponse::success([
            'users' => User::count(),
            'vendors_pending_approval' => VendorProfile::where('verification_status', 'pending')->count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'articles' => Article::count(),
        ]);
    }

    public function users()
    {
        return ApiResponse::success(User::query()->with('roles', 'vendorProfile')->latest()->paginate(20));
    }

    public function orders()
    {
        return ApiResponse::success(Order::query()->with('farmer', 'vendor', 'items')->latest()->paginate(20));
    }

    public function payments()
    {
        return ApiResponse::success(Payment::query()->with('user', 'order')->latest()->paginate(20));
    }

    public function approveVendor(Request $request, VendorProfile $vendorProfile)
    {
        $request->validate(['verification_status' => ['required', 'in:pending,approved,rejected']]);

        $vendorProfile->update([
            'verification_status' => $request->string('verification_status')->toString(),
            'verified_at' => $request->string('verification_status')->toString() === 'approved' ? now() : null,
        ]);

        $this->roleOnboarding->syncApproval(
            $vendorProfile->user,
            RoleType::Vendor->value,
            $vendorProfile->verification_status
        );

        return ApiResponse::success($vendorProfile->refresh(), 'Vendor verification updated.');
    }

    public function approveExpert(Request $request, AgronomistProfile $agronomistProfile)
    {
        $request->validate(['verification_status' => ['required', 'in:pending,approved,rejected']]);

        $agronomistProfile->update([
            'verification_status' => $request->string('verification_status')->toString(),
            'verified_at' => $request->string('verification_status')->toString() === 'approved' ? now() : null,
        ]);

        $this->roleOnboarding->syncApproval(
            $agronomistProfile->user,
            RoleType::Agronomist->value,
            $agronomistProfile->verification_status
        );

        return ApiResponse::success($agronomistProfile->refresh(), 'Expert verification updated.');
    }

    public function moderateProduct(Request $request, Product $product)
    {
        $request->validate(['is_active' => ['required', 'boolean']]);

        $product->update(['is_active' => $request->boolean('is_active')]);

        return ApiResponse::success($product->refresh(), 'Product moderation applied.');
    }
}

<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Services\Auth\RoleOnboardingService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class RoleController extends Controller
{
    public function __construct(private readonly RoleOnboardingService $roleOnboarding) {}

    public function roles(Request $request)
    {
        $user = $request->user()->load('roles', 'vendorProfile', 'agronomistProfile');

        return ApiResponse::success([
            'assigned_roles' => $user->roles->pluck('slug')->values(),
            'statuses' => $this->roleOnboarding->roleStatuses($user),
        ]);
    }

    public function assign(Request $request)
    {
        $payload = $request->validate([
            'role' => ['required', 'string', 'in:farmer,vendor,expert,agronomist'],
            'region' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'farm_size_hectares' => ['nullable', 'numeric'],
            'crop_types' => ['nullable', 'array'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['nullable', 'string', 'max:255'],
            'product_category' => ['nullable', 'string', 'max:255'],
            'vendor_description' => ['nullable', 'string', 'max:1000'],
            'vendor_region' => ['nullable', 'string', 'max:255'],
            'vendor_district' => ['nullable', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'expert_bio' => ['nullable', 'string', 'max:2000'],
            'regions_served' => ['nullable', 'array'],
            'regions_served.*' => ['string', 'max:255'],
        ]);

        $role = $payload['role'] === 'expert' ? RoleType::Agronomist->value : $payload['role'];
        $user = $request->user()->load('roles');

        if ($role === RoleType::Farmer->value) {
            $this->roleOnboarding->assignRole($user, RoleType::Farmer->value);
            $this->roleOnboarding->upsertFarmerProfile($user, $payload);

            return ApiResponse::success(['role' => RoleType::Farmer->value, 'status' => 'approved'], 'Farmer role assigned.');
        }

        if (! in_array($role, [RoleType::Vendor->value, RoleType::Agronomist->value], true)) {
            throw new InvalidArgumentException('Unsupported role assignment.');
        }

        $result = $this->roleOnboarding->applyForRole($user, $role, $payload);

        return ApiResponse::success($result, 'Role application submitted.');
    }

    public function applyVendor(Request $request)
    {
        $payload = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:255'],
            'product_category' => ['required', 'string', 'max:255'],
            'vendor_description' => ['nullable', 'string', 'max:1000'],
            'vendor_region' => ['nullable', 'string', 'max:255'],
            'vendor_district' => ['nullable', 'string', 'max:255'],
            'vendor_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('vendor_document')) {
            $payload['vendor_document_path'] = $this->roleOnboarding->storeVerificationDocument(
                $request->file('vendor_document'),
                'vendor-'.$request->user()->id
            );
        }

        return ApiResponse::success(
            $this->roleOnboarding->applyForRole($request->user(), RoleType::Vendor->value, $payload),
            'Vendor application submitted.'
        );
    }

    public function vendorStatus(Request $request)
    {
        return ApiResponse::success([
            'status' => $request->user()->vendorProfile?->verification_status ?? 'not_applied',
        ]);
    }

    public function applyExpert(Request $request)
    {
        $payload = $request->validate([
            'specialty' => ['required', 'string', 'max:255'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0'],
            'expert_bio' => ['nullable', 'string', 'max:2000'],
            'regions_served' => ['nullable', 'array'],
            'regions_served.*' => ['string', 'max:255'],
            'regions_served_text' => ['nullable', 'string', 'max:500'],
            'certification_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if (($payload['regions_served'] ?? []) === [] && ! empty($payload['regions_served_text'])) {
            $payload['regions_served'] = collect(explode(',', $payload['regions_served_text']))
                ->map(fn (string $region): string => trim($region))
                ->filter()
                ->values()
                ->all();
        }

        if ($request->hasFile('certification_document')) {
            $payload['certification_document_path'] = $this->roleOnboarding->storeVerificationDocument(
                $request->file('certification_document'),
                'expert-'.$request->user()->id
            );
        }

        return ApiResponse::success(
            $this->roleOnboarding->applyForRole($request->user(), RoleType::Agronomist->value, $payload),
            'Expert application submitted.'
        );
    }

    public function expertStatus(Request $request)
    {
        return ApiResponse::success([
            'status' => $request->user()->agronomistProfile?->verification_status ?? 'not_applied',
        ]);
    }
}

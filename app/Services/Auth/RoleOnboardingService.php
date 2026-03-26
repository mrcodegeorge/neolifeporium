<?php

namespace App\Services\Auth;

use App\Enums\RoleType;
use App\Models\AgronomistProfile;
use App\Models\FarmerProfile;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoleOnboardingService
{
    public function normalizeRoles(array $roles): array
    {
        return collect($roles)
            ->filter(fn (mixed $role): bool => is_string($role) && $role !== '')
            ->map(fn (string $role): string => $role === 'expert' ? RoleType::Agronomist->value : $role)
            ->intersect([RoleType::Farmer->value, RoleType::Vendor->value, RoleType::Agronomist->value])
            ->unique()
            ->values()
            ->all();
    }

    public function mapRoleToDashboardRoute(string $role): string
    {
        return match ($role) {
            RoleType::Vendor->value => route('vendor.panel'),
            RoleType::Agronomist->value => route('expert.panel'),
            RoleType::Admin->value, RoleType::SuperAdmin->value => route('admin.panel'),
            default => route('dashboard'),
        };
    }

    public function resolveDashboardRedirect(User $user, ?string $requestedRole = null): string
    {
        $ownedRoles = $user->roles->pluck('slug')->values();

        if ($ownedRoles->isEmpty()) {
            return route('roles.onboarding');
        }

        if ($requestedRole && $ownedRoles->contains($requestedRole)) {
            return $this->mapRoleToDashboardRoute($requestedRole);
        }

        if ($ownedRoles->contains(RoleType::SuperAdmin->value) || $ownedRoles->contains(RoleType::Admin->value)) {
            return route('admin.panel');
        }

        if ($ownedRoles->count() > 1) {
            return route('roles.choose');
        }

        return $this->mapRoleToDashboardRoute($ownedRoles->first());
    }

    public function assignRole(User $user, string $roleSlug): void
    {
        $role = Role::query()->firstOrCreate(
            ['slug' => $roleSlug],
            ['name' => str($roleSlug)->replace('_', ' ')->title()->toString()]
        );

        $user->roles()->syncWithoutDetaching([$role->id]);
    }

    public function registerRolePayloads(User $user, array $payload, array $roles): void
    {
        if (in_array(RoleType::Farmer->value, $roles, true)) {
            $this->assignRole($user, RoleType::Farmer->value);
            $this->upsertFarmerProfile($user, $payload);
        }

        if (in_array(RoleType::Vendor->value, $roles, true)) {
            $this->upsertVendorApplication($user, $payload);
        }

        if (in_array(RoleType::Agronomist->value, $roles, true)) {
            $this->upsertExpertApplication($user, $payload);
        }
    }

    public function upsertFarmerProfile(User $user, array $payload): FarmerProfile
    {
        return FarmerProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'region' => $payload['region'] ?? 'Greater Accra',
                'district' => $payload['district'] ?? null,
                'location' => $payload['location'] ?? null,
                'farm_size_hectares' => $payload['farm_size_hectares'] ?? null,
                'crop_types' => $payload['crop_types'] ?? [],
                'primary_language' => $payload['primary_language'] ?? 'English',
            ]
        );
    }

    public function upsertVendorApplication(User $user, array $payload): VendorProfile
    {
        return VendorProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $payload['business_name'] ?? "{$user->name} Ventures",
                'business_type' => $payload['business_type'] ?? null,
                'product_category' => $payload['product_category'] ?? null,
                'description' => $payload['vendor_description'] ?? $payload['description'] ?? null,
                'region' => $payload['vendor_region'] ?? $payload['region'] ?? null,
                'district' => $payload['vendor_district'] ?? $payload['district'] ?? null,
                'verification_status' => 'pending',
                'verification_document_path' => $payload['vendor_document_path'] ?? null,
                'verified_at' => null,
                'commission_rate' => 7.50,
            ]
        );
    }

    public function upsertExpertApplication(User $user, array $payload): AgronomistProfile
    {
        return AgronomistProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'specialty' => $payload['specialty'] ?? 'General Agronomy',
                'experience_years' => $payload['experience_years'] ?? null,
                'bio' => $payload['expert_bio'] ?? $payload['bio'] ?? null,
                'hourly_rate' => $payload['hourly_rate'] ?? 0,
                'regions_served' => $payload['regions_served'] ?? [$payload['region'] ?? 'Greater Accra'],
                'is_available' => true,
                'verification_status' => 'pending',
                'certification_document_path' => $payload['certification_document_path'] ?? null,
                'verified_at' => null,
            ]
        );
    }

    public function storeVerificationDocument(UploadedFile $file, string $prefix): string
    {
        return $file->storeAs(
            'private/verification',
            $prefix.'-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(4)).'.'.$file->getClientOriginalExtension(),
            'local'
        );
    }

    public function applyForRole(User $user, string $role, array $payload): array
    {
        $normalizedRole = $role === 'expert' ? RoleType::Agronomist->value : $role;

        if (! in_array($normalizedRole, [RoleType::Vendor->value, RoleType::Agronomist->value], true)) {
            throw new InvalidArgumentException('Unsupported role application.');
        }

        return DB::transaction(function () use ($user, $normalizedRole, $payload): array {
            if ($normalizedRole === RoleType::Vendor->value) {
                $profile = $this->upsertVendorApplication($user, $payload);

                return ['role' => $normalizedRole, 'status' => $profile->verification_status];
            }

            $profile = $this->upsertExpertApplication($user, $payload);

            return ['role' => $normalizedRole, 'status' => $profile->verification_status];
        });
    }

    public function roleStatuses(User $user): Collection
    {
        $ownedRoles = $user->roles->pluck('slug');

        return collect([
            [
                'role' => RoleType::Farmer->value,
                'assigned' => $ownedRoles->contains(RoleType::Farmer->value),
                'status' => $ownedRoles->contains(RoleType::Farmer->value) ? 'approved' : 'not_applied',
            ],
            [
                'role' => RoleType::Vendor->value,
                'assigned' => $ownedRoles->contains(RoleType::Vendor->value),
                'status' => $user->vendorProfile?->verification_status ?? 'not_applied',
            ],
            [
                'role' => RoleType::Agronomist->value,
                'assigned' => $ownedRoles->contains(RoleType::Agronomist->value),
                'status' => $user->agronomistProfile?->verification_status ?? 'not_applied',
            ],
        ]);
    }

    public function syncApproval(User $user, string $role, string $status): void
    {
        if ($role === RoleType::Vendor->value) {
            if ($status === 'approved') {
                $this->assignRole($user, RoleType::Vendor->value);
            } else {
                $roleId = Role::query()->where('slug', RoleType::Vendor->value)->value('id');
                if ($roleId) {
                    $user->roles()->detach($roleId);
                }
            }

            return;
        }

        if ($role === RoleType::Agronomist->value) {
            if ($status === 'approved') {
                $this->assignRole($user, RoleType::Agronomist->value);
            } else {
                $roleId = Role::query()->where('slug', RoleType::Agronomist->value)->value('id');
                if ($roleId) {
                    $user->roles()->detach($roleId);
                }
            }
        }
    }
}

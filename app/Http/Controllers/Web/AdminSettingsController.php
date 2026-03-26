<?php

namespace App\Http\Controllers\Web;

use App\Enums\RoleType;
use App\Http\Controllers\Controller;
use App\Models\AgronomistProfile;
use App\Models\FarmerProfile;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    private const SETTINGS_MAP = [
        'site_name' => 'general',
        'app_url' => 'general',
        'default_currency' => 'general',
        'default_timezone' => 'general',
        'maintenance_mode_message' => 'general',
        'paystack_public_key' => 'payments',
        'paystack_secret_key' => 'payments',
        'momo_api_key' => 'payments',
        'momo_api_secret' => 'payments',
        'sms_provider' => 'notifications',
        'sms_api_key' => 'notifications',
    ];

    public function index(Request $request): View
    {
        $settings = Setting::query()
            ->whereIn('key', array_keys(self::SETTINGS_MAP))
            ->pluck('value', 'key');

        $registeredUsers = User::query()
            ->with('roles')
            ->latest()
            ->paginate(20, ['*'], 'users_page')
            ->withQueryString();

        $staffMembers = User::query()
            ->with('roles')
            ->whereHas('roles', fn ($query) => $query->whereIn('slug', ['admin', 'super_admin', 'agronomist']))
            ->latest()
            ->paginate(12, ['*'], 'staff_page')
            ->withQueryString();

        return view('admin.settings', [
            'settings' => $settings,
            'roles' => Role::query()->orderBy('name')->get(),
            'staffMembers' => $staffMembers,
            'registeredUsers' => $registeredUsers,
            'canAssignSuperAdmin' => (bool) $request->user()?->hasRole(RoleType::SuperAdmin->value),
        ]);
    }

    public function updatePlatformSettings(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'site_name' => ['required', 'string', 'max:255'],
            'app_url' => ['required', 'url', 'max:255'],
            'default_currency' => ['required', 'string', 'max:10'],
            'default_timezone' => ['required', 'string', 'max:100'],
            'maintenance_mode_message' => ['nullable', 'string', 'max:500'],
            'paystack_public_key' => ['nullable', 'string', 'max:255'],
            'paystack_secret_key' => ['nullable', 'string', 'max:255'],
            'momo_api_key' => ['nullable', 'string', 'max:255'],
            'momo_api_secret' => ['nullable', 'string', 'max:255'],
            'sms_provider' => ['nullable', 'string', 'max:100'],
            'sms_api_key' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($payload as $key => $value) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['group' => self::SETTINGS_MAP[$key] ?? 'general', 'value' => $value]
            );
        }

        return back()->with('status', 'Platform settings updated.');
    }

    public function storeStaff(Request $request): RedirectResponse
    {
        $allowedStaffRoles = ['admin', 'agronomist'];

        if ($request->user()?->hasRole(RoleType::SuperAdmin->value)) {
            $allowedStaffRoles[] = 'super_admin';
        }

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:'.implode(',', $allowedStaffRoles)],
            'status' => ['required', 'in:active,inactive,suspended'],
        ]);

        DB::transaction(function () use ($payload): void {
            $user = User::query()->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'phone' => $payload['phone'] ?? null,
                'password' => Hash::make($payload['password']),
                'status' => $payload['status'],
                'preferred_channel' => 'email',
            ]);

            $role = Role::query()->firstOrCreate(
                ['slug' => $payload['role']],
                ['name' => str($payload['role'])->replace('_', ' ')->title()->toString()]
            );

            $user->roles()->syncWithoutDetaching([$role->id]);
            $this->syncRoleProfiles($user, [$payload['role']]);
        });

        return back()->with('status', 'Staff account created.');
    }

    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        $payload = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'string', 'in:farmer,vendor,agronomist,admin,super_admin'],
        ]);

        $roles = collect($payload['roles'])->unique()->values();

        if (! $request->user()?->hasRole(RoleType::SuperAdmin->value) && $roles->contains('super_admin')) {
            return back()->withErrors(['roles' => 'Only a super admin can assign super admin role.']);
        }

        $roleIds = $roles->map(function (string $slug) {
            return Role::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => str($slug)->replace('_', ' ')->title()->toString()]
            )->id;
        });

        $user->roles()->sync($roleIds->all());
        $this->syncRoleProfiles($user, $roles->all());

        return back()->with('status', 'User roles updated.');
    }

    private function syncRoleProfiles(User $user, array $roles): void
    {
        if (in_array('farmer', $roles, true) && ! $user->farmerProfile()->exists()) {
            FarmerProfile::query()->create([
                'user_id' => $user->id,
                'region' => 'Greater Accra',
                'primary_language' => 'English',
                'crop_types' => [],
            ]);
        }

        if (in_array('vendor', $roles, true) && ! $user->vendorProfile()->exists()) {
            VendorProfile::query()->create([
                'user_id' => $user->id,
                'business_name' => "{$user->name} Ventures",
                'verification_status' => 'pending',
                'commission_rate' => 7.50,
            ]);
        }

        if (in_array('agronomist', $roles, true) && ! $user->agronomistProfile()->exists()) {
            AgronomistProfile::query()->create([
                'user_id' => $user->id,
                'specialty' => 'General Agronomy',
                'bio' => 'Profile created by admin.',
                'hourly_rate' => 0,
                'regions_served' => ['Greater Accra'],
                'is_available' => true,
            ]);
        }
    }
}

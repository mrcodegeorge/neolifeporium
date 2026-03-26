<?php

namespace Tests\Feature;

use App\Enums\RoleType;
use App\Models\Role;
use App\Models\User;
use App\Models\VendorProfile;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleOnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_application_moves_from_pending_to_approved_and_assigns_role(): void
    {
        $this->seed(RoleSeeder::class);

        $farmerRole = Role::query()->where('slug', RoleType::Farmer->value)->firstOrFail();
        $adminRole = Role::query()->where('slug', RoleType::Admin->value)->firstOrFail();

        $user = User::factory()->create();
        $user->roles()->attach($farmerRole->id);

        Sanctum::actingAs($user);

        $this->postJson('/api/vendor/apply', [
            'business_name' => 'Greenline Inputs',
            'business_type' => 'Retail',
            'product_category' => 'Seeds',
        ])->assertOk()->assertJsonPath('data.status', 'pending');

        $vendorProfile = VendorProfile::query()->where('user_id', $user->id)->firstOrFail();

        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);
        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/admin/vendors/'.$vendorProfile->id.'/approval', [
            'verification_status' => 'approved',
        ])->assertOk();

        $this->assertTrue($user->fresh()->roles()->where('slug', RoleType::Vendor->value)->exists());
    }

    public function test_user_can_switch_active_role(): void
    {
        $this->seed(RoleSeeder::class);

        $user = User::factory()->create();
        $roles = Role::query()->whereIn('slug', [RoleType::Farmer->value, RoleType::Vendor->value])->pluck('id');
        $user->roles()->sync($roles);

        $this->actingAs($user)
            ->post(route('roles.switch'), ['role' => RoleType::Vendor->value])
            ->assertRedirect(route('vendor.panel'));

        $this->assertSame(RoleType::Vendor->value, session('active_role'));
    }
}

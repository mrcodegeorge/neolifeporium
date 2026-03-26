<?php

namespace App\Services\Auth;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(private readonly RoleOnboardingService $roleOnboarding) {}

    public function register(array $payload): User
    {
        return DB::transaction(function () use ($payload) {
            $roles = $this->roleOnboarding->normalizeRoles($payload['roles'] ?? [$payload['role'] ?? RoleType::Farmer->value]);

            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'] ?? null,
                'phone' => $payload['phone'] ?? null,
                'password' => Hash::make($payload['password']),
                'preferred_channel' => $payload['preferred_channel'] ?? 'email',
            ]);

            $this->roleOnboarding->registerRolePayloads($user, $payload, $roles);

            return $user->load('roles', 'farmerProfile', 'vendorProfile', 'agronomistProfile');
        });
    }

    public function login(array $credentials): array
    {
        $user = User::query()
            ->with('roles')
            ->where('email', $credentials['login'])
            ->orWhere('phone', $credentials['login'])
            ->firstOrFail();

        abort_unless(Hash::check($credentials['password'], $user->password), 422, 'Invalid credentials.');

        $user->forceFill(['last_login_at' => now()])->save();

        return [
            'user' => $user,
            'token' => $user->createToken('neolifeporium')->plainTextToken,
        ];
    }
}

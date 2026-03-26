<?php

namespace Tests\Feature;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_via_api(): void
    {
        $this->seed(RoleSeeder::class);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Ama Boateng',
            'email' => 'ama@example.com',
            'phone' => '+233200000001',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'farmer',
            'region' => 'Eastern',
            'crop_types' => ['cassava'],
        ]);

        $response->assertCreated()->assertJsonPath('data.email', 'ama@example.com');
    }
}

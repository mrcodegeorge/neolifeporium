<?php

namespace Tests\Feature;

use Database\Seeders\NeolifeporiumSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed([RoleSeeder::class, NeolifeporiumSeeder::class]);

        $response = $this->get('/');

        $response->assertOk();
    }
}

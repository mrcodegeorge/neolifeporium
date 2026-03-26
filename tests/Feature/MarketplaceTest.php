<?php

namespace Tests\Feature;

use Database\Seeders\NeolifeporiumSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads(): void
    {
        $this->seed([RoleSeeder::class, NeolifeporiumSeeder::class]);

        $response = $this->get('/');

        $response->assertOk()->assertSee('Neolifeporium');
    }

    public function test_products_api_returns_seeded_product(): void
    {
        $this->seed([RoleSeeder::class, NeolifeporiumSeeder::class]);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()->assertJsonFragment(['name' => 'Hybrid Maize Starter Pack']);
    }
}

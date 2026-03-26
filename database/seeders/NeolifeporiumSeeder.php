<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Enums\RoleType;
use App\Models\AgronomistProfile;
use App\Models\Article;
use App\Models\Category;
use App\Models\ExpertSpecialization;
use App\Models\FarmerProfile;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Models\VendorProfile;
use App\Models\WeatherInsight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NeolifeporiumSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::pluck('id', 'slug');

        $farmer = User::updateOrCreate(
            ['email' => 'farmer@neolifeporium.test'],
            [
                'name' => 'Kojo Mensah',
                'phone' => '+233201112233',
                'password' => Hash::make('password'),
                'preferred_channel' => 'sms',
            ]
        );
        $farmer->roles()->sync([$roles[RoleType::Farmer->value]]);
        FarmerProfile::updateOrCreate(['user_id' => $farmer->id], [
            'region' => 'Ashanti',
            'district' => 'Ejisu',
            'location' => 'Ejisu Market Cluster',
            'farm_size_hectares' => 4.5,
            'crop_types' => ['maize', 'tomato', 'pepper'],
            'primary_language' => 'Twi',
        ]);

        $vendor = User::updateOrCreate(
            ['email' => 'vendor@neolifeporium.test'],
            [
                'name' => 'Green Harvest Supplies',
                'phone' => '+233244556677',
                'password' => Hash::make('password'),
            ]
        );
        $vendor->roles()->sync([$roles[RoleType::Vendor->value]]);
        VendorProfile::updateOrCreate(['user_id' => $vendor->id], [
            'business_name' => 'Green Harvest Supplies',
            'description' => 'Certified seed, irrigation kits, and post-harvest tools for smallholder farms.',
            'region' => 'Greater Accra',
            'district' => 'Ga East',
            'verification_status' => 'approved',
            'verified_at' => now(),
            'commission_rate' => 7.5,
        ]);

        $expert = User::updateOrCreate(
            ['email' => 'expert@neolifeporium.test'],
            [
                'name' => 'Dr. Abena Owusu',
                'phone' => '+233277889900',
                'password' => Hash::make('password'),
            ]
        );
        $expert->roles()->sync([$roles[RoleType::Agronomist->value]]);
        AgronomistProfile::updateOrCreate(['user_id' => $expert->id], [
            'specialty' => 'Soil fertility and vegetable crops',
            'bio' => 'Field agronomist supporting Ghanaian and West African vegetable growers.',
            'hourly_rate' => 180,
            'regions_served' => ['Ashanti', 'Greater Accra', 'Eastern'],
            'is_available' => true,
        ]);
        foreach (['maize', 'vegetables', 'irrigation'] as $specialization) {
            ExpertSpecialization::updateOrCreate(
                ['expert_id' => $expert->id, 'name' => $specialization]
            );
        }

        $admin = User::updateOrCreate(
            ['email' => 'admin@neolifeporium.test'],
            [
                'name' => 'Neolifeporium Admin',
                'phone' => '+233209998887',
                'password' => Hash::make('password'),
            ]
        );
        $admin->roles()->sync([$roles[RoleType::SuperAdmin->value]]);

        $categories = [
            ['name' => 'Seeds & Inputs', 'slug' => 'seeds-inputs', 'crop_type' => 'mixed'],
            ['name' => 'Irrigation', 'slug' => 'irrigation', 'crop_type' => 'horticulture'],
            ['name' => 'Digital Advisory', 'slug' => 'digital-advisory', 'crop_type' => 'mixed'],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(['slug' => $categoryData['slug']], $categoryData);
        }

        $products = [
            [
                'name' => 'Hybrid Maize Starter Pack',
                'sku' => 'NLP-MAIZE-001',
                'product_type' => ProductType::Physical->value,
                'description' => 'Drought-aware hybrid maize seed bundle with starter fertilizer guidance.',
                'short_description' => 'Seed + nutrient starter for smallholder maize plots.',
                'price' => 145,
                'inventory' => 80,
                'crop_type' => 'maize',
                'region' => 'Ashanti',
                'category_slug' => 'seeds-inputs',
                'is_featured' => true,
            ],
            [
                'name' => 'Solar Drip Irrigation Kit',
                'sku' => 'NLP-IRR-010',
                'product_type' => ProductType::Physical->value,
                'description' => 'Low-pressure drip kit designed for low-bandwidth, low-resource farm operations.',
                'short_description' => 'Water-saving irrigation for 1-2 hectare vegetable farms.',
                'price' => 980,
                'inventory' => 20,
                'crop_type' => 'tomato',
                'region' => 'Greater Accra',
                'category_slug' => 'irrigation',
                'is_featured' => true,
            ],
            [
                'name' => 'Weather-Smart Advisory Subscription',
                'sku' => 'NLP-DIG-115',
                'product_type' => ProductType::Digital->value,
                'description' => 'Receive crop-stage recommendations based on weather, pests, and local agronomy.',
                'short_description' => 'Digital farming intelligence for Ghanaian growers.',
                'price' => 69,
                'inventory' => 999,
                'crop_type' => 'mixed',
                'region' => 'National',
                'category_slug' => 'digital-advisory',
                'is_featured' => true,
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category_slug'])->firstOrFail();

            $product = Product::updateOrCreate(
                ['sku' => $productData['sku']],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'product_type' => $productData['product_type'],
                    'description' => $productData['description'],
                    'short_description' => $productData['short_description'],
                    'price' => $productData['price'],
                    'currency' => 'GHS',
                    'inventory' => $productData['inventory'],
                    'crop_type' => $productData['crop_type'],
                    'region' => $productData['region'],
                    'is_active' => true,
                    'is_featured' => $productData['is_featured'],
                ]
            );

            ProductImage::updateOrCreate(
                ['product_id' => $product->id, 'path' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=900&q=80'],
                ['alt_text' => $product->name, 'is_primary' => true]
            );
        }

        $article = Article::updateOrCreate(
            ['slug' => 'rain-ready-maize-farming-ghana'],
            [
                'author_id' => $expert->id,
                'title' => 'Rain-Ready Maize Farming for Ghanaian Growers',
                'excerpt' => 'How to align seed choice, fertilizer timing, and drainage before the next rain cycle.',
                'body' => '<p>Prepare seedbeds early, check drainage channels, and align seed choice with expected rainfall intensity. Use shorter-cycle hybrids in drought-prone zones.</p>',
                'cover_image' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=900&q=80',
                'meta_title' => 'Rain-Ready Maize Farming Ghana',
                'meta_description' => 'Weather-smart maize farming tips for growers across Ghana.',
                'crop_tags' => ['maize'],
                'region_tags' => ['Ashanti', 'Northern'],
                'published_at' => now()->subDays(2),
                'is_published' => true,
            ]
        );

        $categoryTags = [
            ['name' => 'Crop farming', 'slug' => 'crop-farming', 'type' => 'category'],
            ['name' => 'Livestock', 'slug' => 'livestock', 'type' => 'category'],
            ['name' => 'Agritech tools', 'slug' => 'agritech-tools', 'type' => 'category'],
            ['name' => 'Climate-smart agriculture', 'slug' => 'climate-smart-agriculture', 'type' => 'category'],
            ['name' => 'maize', 'slug' => 'maize', 'type' => 'crop'],
            ['name' => 'cassava', 'slug' => 'cassava', 'type' => 'crop'],
            ['name' => 'poultry', 'slug' => 'poultry', 'type' => 'crop'],
            ['name' => 'Ashanti', 'slug' => 'ashanti', 'type' => 'region'],
            ['name' => 'Greater Accra', 'slug' => 'greater-accra', 'type' => 'region'],
        ];

        foreach ($categoryTags as $tagData) {
            Tag::updateOrCreate(['slug' => $tagData['slug']], $tagData);
        }

        $article->tags()->syncWithoutDetaching(
            Tag::query()->whereIn('slug', ['crop-farming', 'climate-smart-agriculture', 'maize', 'ashanti'])->pluck('id')->all()
        );

        $recommendedProducts = Product::query()->whereIn('slug', ['hybrid-maize-starter-pack', 'solar-drip-irrigation-kit'])->pluck('id')->all();
        $article->recommendedProducts()->syncWithoutDetaching($recommendedProducts);

        WeatherInsight::updateOrCreate(
            ['location' => 'Accra', 'weather_date' => now()->toDateString()],
            [
                'region' => 'Greater Accra',
                'summary' => 'Warm afternoon with moderate rain chance',
                'rainfall_probability' => 62,
                'temperature_celsius' => 31.4,
                'alert_level' => 'medium',
                'recommendations' => [
                    'Schedule transplanting for late afternoon after expected showers.',
                    'Promote moisture-retention inputs and drip kits in dry pockets.',
                ],
            ]
        );
    }
}

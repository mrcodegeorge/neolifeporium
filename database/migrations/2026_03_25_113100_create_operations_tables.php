<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 10)->default('GHS');
            $table->json('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->index(['farmer_id', 'status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('cover_image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('crop_tags')->nullable();
            $table->json('region_tags')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_published')->default(false)->index();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agronomist_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('scheduled_for')->index();
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->string('session_type')->default('chat');
            $table->string('status')->default('pending')->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('topic');
            $table->text('notes')->nullable();
            $table->string('meeting_link')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('channel')->default('in_app');
            $table->string('title');
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('weather_insights', function (Blueprint $table) {
            $table->id();
            $table->string('region')->index();
            $table->string('location')->index();
            $table->date('weather_date')->index();
            $table->string('summary');
            $table->unsignedTinyInteger('rainfall_probability')->default(0);
            $table->decimal('temperature_celsius', 5, 2)->nullable();
            $table->string('alert_level')->default('normal')->index();
            $table->json('recommendations')->nullable();
            $table->json('source_payload')->nullable();
            $table->timestamps();
            $table->unique(['location', 'weather_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_insights');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

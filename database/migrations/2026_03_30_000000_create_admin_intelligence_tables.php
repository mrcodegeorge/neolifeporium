<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('admin_insights')) {
            Schema::create('admin_insights', function (Blueprint $table) {
                $table->id();
                $table->string('type')->index();
                $table->string('title');
                $table->text('message');
                $table->string('severity')->default('info')->index();
                $table->json('context')->nullable();
                $table->timestamp('observed_at')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_alerts')) {
            Schema::create('admin_alerts', function (Blueprint $table) {
                $table->id();
                $table->string('type')->index();
                $table->string('title');
                $table->text('message');
                $table->string('severity')->default('warning')->index();
                $table->json('context')->nullable();
                $table->timestamp('resolved_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_audit_logs')) {
            Schema::create('admin_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('action')->index();
                $table->string('method', 12);
                $table->string('path', 191);
                $table->string('ip_address', 64)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('inventory_flags')) {
            Schema::create('inventory_flags', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->index();
                $table->unsignedBigInteger('vendor_id')->nullable()->index();
                $table->string('type')->index();
                $table->string('status')->default('open')->index();
                $table->json('details')->nullable();
                $table->timestamp('detected_at')->index();
                $table->timestamp('resolved_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('forecast_snapshots')) {
            Schema::create('forecast_snapshots', function (Blueprint $table) {
                $table->id();
                $table->string('type')->index();
                $table->date('window_start')->index();
                $table->date('window_end')->index();
                $table->unsignedInteger('horizon_days')->default(30);
                $table->json('data');
                $table->timestamp('generated_at')->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('forecast_snapshots');
        Schema::dropIfExists('inventory_flags');
        Schema::dropIfExists('admin_audit_logs');
        Schema::dropIfExists('admin_alerts');
        Schema::dropIfExists('admin_insights');
    }
};

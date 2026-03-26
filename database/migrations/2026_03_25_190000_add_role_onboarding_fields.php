<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_profiles', 'business_type')) {
                $table->string('business_type')->nullable()->after('business_name');
            }

            if (! Schema::hasColumn('vendor_profiles', 'product_category')) {
                $table->string('product_category')->nullable()->after('business_type');
            }
        });

        Schema::table('agronomist_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('agronomist_profiles', 'experience_years')) {
                $table->unsignedSmallInteger('experience_years')->nullable()->after('specialty');
            }

            if (! Schema::hasColumn('agronomist_profiles', 'verification_status')) {
                $table->string('verification_status')->default('pending')->index()->after('is_available');
            }

            if (! Schema::hasColumn('agronomist_profiles', 'certification_document_path')) {
                $table->string('certification_document_path')->nullable()->after('verification_status');
            }

            if (! Schema::hasColumn('agronomist_profiles', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('certification_document_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agronomist_profiles', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['experience_years', 'verification_status', 'certification_document_path', 'verified_at'] as $column) {
                if (Schema::hasColumn('agronomist_profiles', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });

        Schema::table('vendor_profiles', function (Blueprint $table) {
            $dropColumns = [];

            foreach (['business_type', 'product_category'] as $column) {
                if (Schema::hasColumn('vendor_profiles', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

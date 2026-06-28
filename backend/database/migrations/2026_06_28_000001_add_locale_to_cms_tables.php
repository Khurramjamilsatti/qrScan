<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landing_features', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('id');
            $table->index(['locale', 'is_active', 'sort_order']);
        });

        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('id');
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'locale']);
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('id');
            $table->index(['locale', 'is_active', 'sort_order']);
        });

        Schema::table('site_pages', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('id');
            $table->dropUnique(['slug']);
            $table->unique(['slug', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::table('site_pages', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale']);
            $table->unique(['slug']);
            $table->dropColumn('locale');
        });

        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropIndex(['locale', 'is_active', 'sort_order']);
            $table->dropColumn('locale');
        });

        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropUnique(['slug', 'locale']);
            $table->unique(['slug']);
            $table->dropColumn('locale');
        });

        Schema::table('landing_features', function (Blueprint $table) {
            $table->dropIndex(['locale', 'is_active', 'sort_order']);
            $table->dropColumn('locale');
        });
    }
};

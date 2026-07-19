<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_funnels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('goal')->default('lead');
            $table->text('description')->nullable();
            $table->string('theme_color')->default('#673ab7');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('conversion_count')->default(0);
            $table->unsignedInteger('entry_count')->default(0);
            $table->timestamps();
        });

        Schema::create('qr_funnel_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funnel_id')->constrained('qr_funnels')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('step_type');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('target_type')->default('url');
            $table->string('target_slug')->nullable();
            $table->string('target_url')->nullable();
            $table->string('cta_text')->nullable();
            $table->timestamps();
        });

        Schema::create('qr_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('accessable_type');
            $table->unsignedBigInteger('accessable_id');
            $table->string('ip_hash', 64);
            $table->string('access_type')->default('scan');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['accessable_type', 'accessable_id', 'ip_hash']);
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            $table->foreignId('funnel_id')->nullable()->after('destination_url')->constrained('qr_funnels')->nullOnDelete();
            $table->json('routing_rules')->nullable()->after('funnel_id');
            $table->json('security')->nullable()->after('routing_rules');
            $table->timestamp('expires_at')->nullable()->after('security');
            $table->unsignedInteger('max_scans')->default(0)->after('expires_at');
            $table->string('signing_secret', 64)->nullable()->after('max_scans');
        });

        Schema::table('short_links', function (Blueprint $table) {
            $table->foreignId('funnel_id')->nullable()->after('destination_url')->constrained('qr_funnels')->nullOnDelete();
            $table->json('routing_rules')->nullable()->after('funnel_id');
            $table->json('security')->nullable()->after('routing_rules');
            $table->unsignedInteger('max_scans')->default(0)->after('security');
            $table->string('signing_secret', 64)->nullable()->after('max_scans');
        });
    }

    public function down(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('funnel_id');
            $table->dropColumn(['routing_rules', 'security', 'max_scans', 'signing_secret']);
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('funnel_id');
            $table->dropColumn(['routing_rules', 'security', 'expires_at', 'max_scans', 'signing_secret']);
        });

        Schema::dropIfExists('qr_access_logs');
        Schema::dropIfExists('qr_funnel_steps');
        Schema::dropIfExists('qr_funnels');
    }
};

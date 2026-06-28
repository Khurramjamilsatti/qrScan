<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('destination_url');
            $table->string('foreground_color')->default('#000000');
            $table->string('background_color')->default('#ffffff');
            $table->string('logo_path')->nullable();
            $table->unsignedInteger('scan_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('short_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->text('destination_url');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->unsignedInteger('click_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('full_name');
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('photo_path')->nullable();
            $table->json('social_links')->nullable();
            $table->string('theme_color')->default('#6366f1');
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('eventable_type');
            $table->unsignedBigInteger('eventable_id');
            $table->string('event_type');
            $table->string('ip_address')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('referrer')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['eventable_type', 'eventable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('business_cards');
        Schema::dropIfExists('short_links');
        Schema::dropIfExists('qr_codes');
    }
};

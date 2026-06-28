<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('template')->default('classic');
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('issuer_name')->nullable();
            $table->string('badge_id')->nullable();
            $table->text('description')->nullable();
            $table->json('skills')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('verify_url')->nullable();
            $table->json('settings')->nullable();
            $table->string('theme_color', 7)->default('#e8655a');
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('badge_image_path')->nullable();
            $table->string('qr_shape')->default('square');
            $table->string('dot_style')->default('square');
            $table->string('corner_style')->default('sharp');
            $table->string('frame_style')->default('none');
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('digital_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('event_name');
            $table->string('event_date')->nullable();
            $table->string('event_time')->nullable();
            $table->string('venue')->nullable();
            $table->string('holder_name');
            $table->string('holder_email')->nullable();
            $table->string('ticket_type')->default('general');
            $table->string('seat_section')->nullable();
            $table->string('seat_row')->nullable();
            $table->string('seat_number')->nullable();
            $table->string('order_id')->nullable();
            $table->string('barcode')->nullable();
            $table->string('template')->default('concert');
            $table->text('terms')->nullable();
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_until')->nullable();
            $table->string('status')->default('valid');
            $table->string('theme_color', 7)->default('#e8655a');
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('qr_shape')->default('square');
            $table->string('dot_style')->default('square');
            $table->string('corner_style')->default('sharp');
            $table->string('frame_style')->default('none');
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('check_in_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('scan_to_win_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('template')->default('instant');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('max_plays_per_day')->default(1);
            $table->string('win_message')->nullable();
            $table->string('lose_message')->nullable();
            $table->text('terms')->nullable();
            $table->json('prizes')->nullable();
            $table->unsignedInteger('total_plays')->default(0);
            $table->unsignedInteger('total_wins')->default(0);
            $table->string('theme_color', 7)->default('#e8655a');
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('qr_shape')->default('square');
            $table->string('dot_style')->default('square');
            $table->string('corner_style')->default('sharp');
            $table->string('frame_style')->default('none');
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('scan_to_win_plays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_to_win_campaign_id')->constrained()->cascadeOnDelete();
            $table->string('ip_hash', 64);
            $table->boolean('won')->default(false);
            $table->string('prize_name')->nullable();
            $table->timestamps();
            $table->index(['scan_to_win_campaign_id', 'ip_hash', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_to_win_plays');
        Schema::dropIfExists('scan_to_win_campaigns');
        Schema::dropIfExists('digital_tickets');
        Schema::dropIfExists('digital_badges');
    }
};

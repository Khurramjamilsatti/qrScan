<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('fields')->nullable();
            $table->json('settings')->nullable();
            $table->string('theme_color', 7)->default('#673ab7');
            $table->string('background_color', 7)->nullable();
            $table->string('header_image_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('qr_shape')->default('square');
            $table->string('dot_style')->default('square');
            $table->string('corner_style')->default('sharp');
            $table->string('frame_style')->default('none');
            $table->boolean('is_active')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('submission_count')->default(0);
            $table->timestamp('closes_at')->nullable();
            $table->unsignedInteger('max_submissions')->default(0);
            $table->unsignedInteger('max_submissions_per_respondent')->default(1);
            $table->timestamps();
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->json('data');
            $table->string('respondent_email')->nullable();
            $table->string('ip_hash', 64);
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['form_id', 'ip_hash']);
            $table->index(['form_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('forms');
    }
};

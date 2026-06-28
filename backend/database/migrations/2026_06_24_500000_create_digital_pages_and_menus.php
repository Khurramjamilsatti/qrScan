<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('template')->default('landing');
            $table->json('content')->nullable();
            $table->string('theme_color', 7)->default('#e8655a');
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        Schema::create('digital_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('theme_color', 7)->default('#e8655a');
            $table->string('currency', 3)->default('USD');
            $table->string('location')->nullable();
            $table->string('phone')->nullable();
            $table->string('hours')->nullable();
            $table->json('sections')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_menus');
        Schema::dropIfExists('digital_pages');
    }
};

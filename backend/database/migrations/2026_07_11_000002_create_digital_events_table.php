<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('template')->default('simple-invite');
            $table->string('event_type')->default('general');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('hosts')->nullable();
            $table->dateTime('event_date')->nullable();
            $table->dateTime('event_end_date')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('dress_code')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('theme_color', 7)->default('#e8655a');
            $table->json('content')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->string('qr_shape', 20)->default('square');
            $table->string('dot_style', 20)->default('square');
            $table->string('corner_style', 20)->default('sharp');
            $table->string('frame_style', 20)->default('none');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_events');
    }
};

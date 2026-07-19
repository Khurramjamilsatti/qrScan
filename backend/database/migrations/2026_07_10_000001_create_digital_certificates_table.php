<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('digital_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('certificate_id')->unique();
            $table->string('template')->default('classic');
            $table->string('title');
            $table->string('recipient_name');
            $table->string('recipient_email')->nullable();
            $table->string('award_title')->nullable();
            $table->string('issuer_name')->nullable();
            $table->text('description')->nullable();
            $table->date('completion_date')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('valid');
            $table->string('theme_color', 7)->default('#1a1333');
            $table->string('logo_path')->nullable();
            $table->string('seal_path')->nullable();
            $table->string('instructor_signature_path')->nullable();
            $table->string('organization_signature_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('settings')->nullable();
            $table->string('qr_shape')->default('square');
            $table->string('dot_style')->default('square');
            $table->string('corner_style')->default('sharp');
            $table->string('frame_style')->default('none');
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_certificates');
    }
};

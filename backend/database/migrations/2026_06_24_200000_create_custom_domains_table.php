<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->string('verification_token')->unique();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::table('qr_codes', function (Blueprint $table) {
            $table->foreignId('custom_domain_id')->nullable()->after('user_id')->constrained('custom_domains')->nullOnDelete();
            $table->string('background_image_path')->nullable()->after('logo_path');
        });

        Schema::table('short_links', function (Blueprint $table) {
            $table->foreignId('custom_domain_id')->nullable()->after('user_id')->constrained('custom_domains')->nullOnDelete();
        });

        Schema::table('business_cards', function (Blueprint $table) {
            $table->foreignId('custom_domain_id')->nullable()->after('user_id')->constrained('custom_domains')->nullOnDelete();
            $table->string('background_image_path')->nullable()->after('photo_path');
            $table->string('logo_path')->nullable()->after('background_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_domain_id');
            $table->dropColumn(['background_image_path', 'logo_path']);
        });
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_domain_id');
        });
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('custom_domain_id');
            $table->dropColumn('background_image_path');
        });
        Schema::dropIfExists('custom_domains');
    }
};

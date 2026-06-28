<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->unsignedSmallInteger('size')->default(400)->after('background_color');
            $table->string('error_correction')->default('M')->after('size');
            $table->unsignedTinyInteger('margin')->default(2)->after('error_correction');
            $table->string('dot_style')->default('square')->after('margin');
        });

        Schema::table('short_links', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('utm_term')->nullable()->after('utm_campaign');
            $table->string('utm_content')->nullable()->after('utm_term');
            $table->timestamp('expires_at')->nullable()->after('is_active');
        });

        Schema::table('business_cards', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('company');
            $table->string('address')->nullable()->after('bio');
            $table->string('tagline')->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn(['size', 'error_correction', 'margin', 'dot_style']);
        });
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn(['description', 'utm_term', 'utm_content', 'expires_at']);
        });
        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn(['bio', 'address', 'tagline']);
        });
    }
};

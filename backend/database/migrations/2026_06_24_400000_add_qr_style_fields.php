<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->string('qr_shape')->default('square')->after('dot_style');
            $table->string('corner_style')->default('sharp')->after('qr_shape');
            $table->string('frame_style')->default('none')->after('corner_style');
        });

        Schema::table('short_links', function (Blueprint $table) {
            $table->string('qr_shape')->default('square')->after('margin');
            $table->string('dot_style')->default('square')->after('qr_shape');
            $table->string('corner_style')->default('sharp')->after('dot_style');
            $table->string('frame_style')->default('none')->after('corner_style');
        });

        Schema::table('business_cards', function (Blueprint $table) {
            $table->string('qr_shape')->default('square')->after('theme_color');
            $table->string('dot_style')->default('square')->after('qr_shape');
            $table->string('corner_style')->default('sharp')->after('dot_style');
            $table->string('frame_style')->default('none')->after('corner_style');
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropColumn(['qr_shape', 'corner_style', 'frame_style']);
        });

        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn(['qr_shape', 'dot_style', 'corner_style', 'frame_style']);
        });

        Schema::table('business_cards', function (Blueprint $table) {
            $table->dropColumn(['qr_shape', 'dot_style', 'corner_style', 'frame_style']);
        });
    }
};

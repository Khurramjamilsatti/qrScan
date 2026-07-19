<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_menus', function (Blueprint $table) {
            $table->string('template')->default('classic')->after('slug');
            $table->string('qr_shape')->default('square')->after('background_image_path');
            $table->string('dot_style')->default('square')->after('qr_shape');
            $table->string('corner_style')->default('sharp')->after('dot_style');
            $table->string('frame_style')->default('none')->after('corner_style');
        });
    }

    public function down(): void
    {
        Schema::table('digital_menus', function (Blueprint $table) {
            $table->dropColumn(['template', 'qr_shape', 'dot_style', 'corner_style', 'frame_style']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->string('foreground_color')->default('#1a1333')->after('expires_at');
            $table->string('background_color')->default('#ffffff')->after('foreground_color');
            $table->string('logo_path')->nullable()->after('background_color');
            $table->string('background_image_path')->nullable()->after('logo_path');
            $table->unsignedSmallInteger('qr_size')->default(400)->after('background_image_path');
            $table->string('error_correction')->default('M')->after('qr_size');
            $table->unsignedTinyInteger('margin')->default(2)->after('error_correction');
        });
    }

    public function down(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn([
                'foreground_color',
                'background_color',
                'logo_path',
                'background_image_path',
                'qr_size',
                'error_correction',
                'margin',
            ]);
        });
    }
};

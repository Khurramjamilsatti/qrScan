<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->string('plan')->default('free')->after('is_admin');
            $table->unsignedInteger('scans_this_month')->default(0)->after('plan');
            $table->timestamp('scans_reset_at')->nullable()->after('scans_this_month');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'plan', 'scans_this_month', 'scans_reset_at']);
        });
    }
};

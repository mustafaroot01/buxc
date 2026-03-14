<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendance_sync_logs', function (Blueprint $table) {
            $table->string('device_model')->nullable()->after('device_id');
            $table->string('os_version')->nullable()->after('device_model');
            $table->string('app_version')->nullable()->after('os_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sync_logs', function (Blueprint $table) {
            $table->dropColumn(['device_model', 'os_version', 'app_version']);
        });
    }
};

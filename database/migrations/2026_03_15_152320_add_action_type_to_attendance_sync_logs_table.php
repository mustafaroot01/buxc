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
            $table->string('action_type')->default('scan')->after('lecture_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_sync_logs', function (Blueprint $table) {
            $table->dropColumn('action_type');
        });
    }
};

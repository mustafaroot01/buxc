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
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('device_id')->nullable()->after('check_in_method');
            $table->timestamp('scanned_at')->nullable()->after('device_id');
            $table->binary('request_id', 16)->nullable()->unique()->after('scanned_at');
            
            // Add unique constraint and index as requested
            $table->unique(['lecture_id', 'student_id']);
            $table->index(['lecture_id', 'student_id'], 'lecture_student_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['lecture_id', 'student_id']);
            $table->dropIndex('lecture_student_index');
            $table->dropColumn(['device_id', 'scanned_at', 'request_id']);
        });
    }
};

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
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedInteger('consecutive_absences')->default(0)->after('group_id');
        });

        Schema::table('warnings', function (Blueprint $table) {
            $table->timestamp('resolved_at')->nullable()->after('issued_at');
            $table->uuid('lecture_id')->nullable()->after('student_id'); // Optional link to which lecture triggered it
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('consecutive_absences');
        });

        Schema::table('warnings', function (Blueprint $table) {
            $table->dropColumn(['resolved_at', 'lecture_id']);
        });
    }
};

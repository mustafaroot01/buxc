<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Clean up existing duplicates before applying UNIQUE constraint
        $duplicates = DB::table('attendances')
            ->select('lecture_id', 'student_id')
            ->groupBy('lecture_id', 'student_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();
            
        foreach ($duplicates as $duplicate) {
            // Keep the earliest record
            $keepId = DB::table('attendances')
                ->where('lecture_id', $duplicate->lecture_id)
                ->where('student_id', $duplicate->student_id)
                ->orderBy('created_at')
                ->value('id');
                
            // Delete the rest
            DB::table('attendances')
                ->where('lecture_id', $duplicate->lecture_id)
                ->where('student_id', $duplicate->student_id)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        // 2. Add Unique Constraints and Indexes
        Schema::table('attendances', function (Blueprint $table) {
            // Unique constraint prevents duplicate attendance and acts as an index for (lecture_id, student_id)
            $table->unique(['lecture_id', 'student_id'], 'attendances_lecture_student_unique');
            
            // Composite index for student-centric reports (all lectures attended by a student)
            $table->index(['student_id', 'lecture_id'], 'attendances_student_lecture_index');
            
            // Composite index for date-filtered lecture reports
            $table->index(['lecture_id', 'created_at'], 'attendances_lecture_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique('attendances_lecture_student_unique');
            $table->dropIndex('attendances_student_lecture_index');
            $table->dropIndex('attendances_lecture_created_index');
        });
    }
};

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
        Schema::create('attendances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lecture_id')->constrained('lectures')->onDelete('cascade');
            $table->foreignUuid('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('status', ['present', 'absent', 'excused'])->default('absent');
            $table->timestamp('check_in_at')->nullable();
            $table->enum('check_in_method', ['qr', 'manual'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

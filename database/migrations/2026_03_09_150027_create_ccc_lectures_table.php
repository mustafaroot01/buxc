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
        Schema::create('lectures', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title')->nullable();
            $table->foreignUuid('subject_id')->constrained('subjects')->onDelete('restrict');
            $table->foreignUuid('teacher_id')->constrained('users')->onDelete('restrict');
            $table->foreignUuid('group_id')->constrained('academic_groups')->onDelete('restrict');
            $table->timestamp('start_time')->useCurrent();
            $table->timestamp('end_time')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lectures');
    }
};

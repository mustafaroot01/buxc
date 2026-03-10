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
        Schema::create('academic_group_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignUuid('academic_group_id')->constrained('academic_groups')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['subject_id', 'academic_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_group_subject');
    }
};

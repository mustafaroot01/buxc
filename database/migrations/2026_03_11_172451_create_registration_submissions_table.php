<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('form_id')->constrained('registration_forms')->onDelete('cascade');
            $table->string('first_name');
            $table->string('second_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female']);
            $table->string('student_external_id');
            $table->string('photo_path')->nullable();
            $table->string('qr_payload')->unique();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            // Prevent duplicate submission (same external ID per form)
            $table->unique(['form_id', 'student_external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_submissions');
    }
};

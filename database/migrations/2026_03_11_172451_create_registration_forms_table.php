<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_forms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->foreignUuid('stage_id')->constrained('academic_stages')->onDelete('cascade');
            $table->foreignUuid('group_id')->constrained('academic_groups')->onDelete('cascade');
            $table->enum('study_type', ['morning', 'evening']);
            $table->boolean('is_open')->default(true);
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_forms');
    }
};

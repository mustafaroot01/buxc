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
        Schema::table('users', function (Blueprint $table) {
            $table->string('academic_title')->nullable()->after('bio'); // e.g., Professor, Asst. Prof
            $table->string('degree')->nullable()->after('academic_title'); // e.g., Ph.D, Master
            $table->string('phone_number')->nullable()->after('degree');
            $table->enum('gender', ['male', 'female'])->nullable()->after('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['academic_title', 'degree', 'phone_number', 'gender']);
        });
    }
};

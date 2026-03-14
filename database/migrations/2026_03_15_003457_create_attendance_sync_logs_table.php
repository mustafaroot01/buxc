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
        Schema::create('attendance_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sync_id')->unique()->index();
            $table->string('device_id')->index();
            $table->uuid('lecture_id')->index();
            
            $table->integer('scans_received')->default(0);
            $table->integer('scans_processed')->default(0);
            $table->integer('failed_scans')->default(0);
            
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->integer('duration_ms')->default(0);
            
            $table->string('status'); // success, failed, partial
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sync_logs');
    }
};

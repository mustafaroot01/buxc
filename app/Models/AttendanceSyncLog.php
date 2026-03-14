<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AttendanceSyncLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'sync_id',
        'device_id',
        'lecture_id',
        'scans_received',
        'scans_processed',
        'failed_scans',
        'sent_at',
        'synced_at',
        'duration_ms',
        'status',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'synced_at' => 'datetime',
    ];
}

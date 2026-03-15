<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AttendanceSyncLog extends Model
{
    use HasFactory, HasUuids;

    /*
    |--------------------------------------------------------------------------
    | Primary Key Settings
    |--------------------------------------------------------------------------
    */

    public $incrementing = false;
    protected $keyType = 'string';


    /*
    |--------------------------------------------------------------------------
    | Status Constants
    |--------------------------------------------------------------------------
    */

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_PARTIAL = 'partial';


    /*
    |--------------------------------------------------------------------------
    | Action Type Constants
    |--------------------------------------------------------------------------
    */

    const ACTION_SCAN = 'scan';
    const ACTION_MANUAL = 'manual';


    /*
    |--------------------------------------------------------------------------
    | Mass Assignable Fields
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'sync_id',
        'device_id',
        'device_model',
        'os_version',
        'app_version',
        'lecture_id',
        'action_type',
        'scans_received',
        'scans_processed',
        'failed_scans',
        'sent_at',
        'synced_at',
        'duration_ms',
        'status',
        'error_details',
    ];


    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'sent_at' => 'datetime',
        'synced_at' => 'datetime',
        'error_details' => 'array',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }


    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeManualActions($query)
    {
        return $query->where('action_type', self::ACTION_MANUAL);
    }

    public function scopeScanActions($query)
    {
        return $query->where('action_type', self::ACTION_SCAN);
    }
}

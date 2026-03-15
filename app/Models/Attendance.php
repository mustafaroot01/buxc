<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsArabicActivity;

class Attendance extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsArabicActivity;

    protected $fillable = [
        'lecture_id',
        'student_id',
        'status',
        'check_in_at',
        'check_in_method',
        'device_id',
        'scanned_at',
        'request_id',
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
        'scanned_at' => 'datetime',
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Activity Log Settings
    |--------------------------------------------------------------------------
    */

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return $this->defaultLogOptions()
            ->dontLogAttributes(['request_id']);
    }

    public function getArabicModelLabel(): string
    {
        return 'تحضير طالب';
    }
}


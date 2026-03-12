<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\LogsArabicActivity;

class Lecture extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsArabicActivity;

    protected static function booted()
    {
        static::deleting(function ($lecture) {
            if ($lecture->isForceDeleting()) {
                $lecture->attendances()->forceDelete();
            } else {
                $lecture->attendances()->delete();
            }
        });

        static::restoring(function ($lecture) {
            $lecture->attendances()->withTrashed()->restore();
        });
    }

    public function getArabicModelLabel(): string { return 'محاضرة'; }
    public function getArabicName(): string { return $this->title; }
    public function getArabicLogName(): string { return 'المحاضرات'; }

    protected $fillable = [
        'title',
        'subject_id',
        'teacher_id',
        'group_id',
        'start_time',
        'end_time',
        'status'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $appends = ['date', 'time', 'stage', 'study_type'];

    public function getDateAttribute()
    {
        return $this->start_time ? $this->start_time->format('Y-m-d') : null;
    }

    public function getTimeAttribute()
    {
        return $this->start_time ? $this->start_time->format('H:i') : null;
    }

    public function getStageAttribute()
    {
        // Avoid N+1 issues if possible by ensuring group.stage is eager loaded
        return $this->group ? $this->group->stage : null;
    }

    public function getStudyTypeAttribute()
    {
        return $this->group ? $this->group->study_type : null;
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function group()
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'lecture_id');
    }
}

<?php

namespace App\Models;

use App\Traits\LogsArabicActivity;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, HasUuids, LogsArabicActivity, SoftDeletes;

    public function getArabicModelLabel(): string
    {
        return 'طالب';
    }

    public function getArabicName(): string
    {
        return trim("{$this->first_name} {$this->second_name} {$this->last_name}");
    }

    public function getArabicLogName(): string
    {
        return 'الطلاب';
    }

    protected $fillable = [
        'first_name',
        'second_name',
        'last_name',
        'student_external_id',
        'gender',
        'photo_path',
        'group_id',
        'qr_payload',
        'consecutive_absences',
    ];

    public function group()
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class, 'student_id');
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->second_name} {$this->last_name}");
    }
}

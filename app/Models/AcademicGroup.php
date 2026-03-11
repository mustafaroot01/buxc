<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\LogsArabicActivity;

class AcademicGroup extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsArabicActivity, \App\Traits\InvalidatesAcademicCache;

    public function getArabicModelLabel(): string { return 'مجموعة'; }
    public function getArabicName(): string { return $this->name; }
    public function getArabicLogName(): string { return 'المجموعات'; }

    protected $fillable = ['name', 'stage_id', 'study_type'];


    public function stage()
    {
        return $this->belongsTo(AcademicStage::class, 'stage_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'group_id');
    }
}

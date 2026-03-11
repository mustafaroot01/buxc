<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\LogsArabicActivity;

class Subject extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsArabicActivity, \App\Traits\InvalidatesAcademicCache;

    public function getArabicModelLabel(): string { return 'مادة دراسية'; }
    public function getArabicName(): string { return $this->name; }
    public function getArabicLogName(): string { return 'المواد'; }

    protected $fillable = ['name', 'code', 'teacher_id', 'stage_id'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function stage()
    {
        return $this->belongsTo(AcademicStage::class, 'stage_id');
    }

    public function groups()
    {
        return $this->belongsToMany(AcademicGroup::class);
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'subject_id');
    }
}


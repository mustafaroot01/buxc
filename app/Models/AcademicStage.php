<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\LogsArabicActivity;

class AcademicStage extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsArabicActivity, \App\Traits\InvalidatesAcademicCache;

    public function getArabicModelLabel(): string { return 'مرحلة دراسية'; }
    public function getArabicName(): string { return $this->name; }
    public function getArabicLogName(): string { return 'المراحل'; }

    protected $fillable = ['name', 'description'];


    public function groups()
    {
        return $this->hasMany(AcademicGroup::class, 'stage_id');
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'stage_id');
    }
}


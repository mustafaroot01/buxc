<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Traits\LogsArabicActivity;

class Warning extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsArabicActivity;

    protected $fillable = [
        'student_id',
        'lecture_id',
        'level',
        'reason',
        'issued_at',
        'resolved_at'
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}


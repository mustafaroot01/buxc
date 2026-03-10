<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;

class Attendance extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    protected $fillable = [
        'lecture_id',
        'student_id',
        'status',
        'check_in_at',
        'check_in_method'
    ];

    protected $casts = [
        'check_in_at' => 'datetime',
    ];

    public function lecture()
    {
        return $this->belongsTo(Lecture::class, 'lecture_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}


<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\LogsArabicActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuid, SoftDeletes, HasRoles, LogsArabicActivity;

    public function getArabicModelLabel(): string { return 'مدرس'; }
    public function getArabicName(): string { return $this->full_name ?? $this->email; }
    public function getArabicLogName(): string { return 'الأساتذة'; }


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'teacher_external_id',
        'department',
        'full_name',
        'email',
        'password',
        'photo_path',
        'bio',
        'academic_title',
        'degree',
        'phone_number',
        'gender',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class, 'teacher_id');
    }

    public function lectures()
    {
        return $this->hasMany(Lecture::class, 'teacher_id');
    }
}


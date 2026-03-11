<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'form_id',
        'first_name',
        'second_name',
        'last_name',
        'gender',
        'student_external_id',
        'photo_path',
        'qr_payload',
        'status',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(RegistrationForm::class, 'form_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->second_name} {$this->last_name}");
    }
}

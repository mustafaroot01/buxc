<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationForm extends Model
{
    use HasUuids;

    protected $fillable = [
        'title',
        'stage_id',
        'group_id',
        'study_type',
        'is_open',
        'slug',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(AcademicStage::class, 'stage_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AcademicGroup::class, 'group_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(RegistrationSubmission::class, 'form_id');
    }
}

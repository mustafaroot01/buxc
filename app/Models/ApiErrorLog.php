<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ApiErrorLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'method',
        'url',
        'payload',
        'status_code',
        'message',
        'exception_class',
        'stack_trace',
        'ip_address',
        'device_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'stack_trace' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

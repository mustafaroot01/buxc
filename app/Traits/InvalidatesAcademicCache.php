<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait InvalidatesAcademicCache
{
    protected static function bootInvalidatesAcademicCache()
    {
        $incrementVersion = function () {
            // Increment the cache version safely
            $version = Cache::get('academic_structure_version', 1);
            Cache::forever('academic_structure_version', $version + 1);
        };

        static::saved($incrementVersion);
        static::deleted($incrementVersion);
    }
}

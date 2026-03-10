<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

trait LogsArabicActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(function (string $eventName) {
                $modelLabel = $this->getArabicModelLabel();

                return match ($eventName) {
                    'created'  => "تم إضافة {$modelLabel}: «{$this->getArabicName()}»",
                    'updated'  => "تم تعديل {$modelLabel}: «{$this->getArabicName()}»",
                    'deleted'  => "تم حذف {$modelLabel}: «{$this->getArabicName()}»",
                    'restored' => "تم استعادة {$modelLabel}: «{$this->getArabicName()}»",
                    default    => "{$modelLabel}: {$eventName}",
                };
            })
            ->useLogName($this->getArabicLogName());
    }

    /**
     * Override in each model to provide a human-readable Arabic model label.
     */
    public function getArabicModelLabel(): string
    {
        return 'سجل';
    }

    /**
     * Override in each model to provide the display name used in log descriptions.
     */
    public function getArabicName(): string
    {
        return $this->name ?? $this->full_name ?? $this->getKey();
    }

    /**
     * Override in each model to provide a log group name.
     */
    public function getArabicLogName(): string
    {
        return 'النظام';
    }
}

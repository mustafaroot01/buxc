<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $lectureId;

    public function __construct($lectureId = null)
    {
        $this->lectureId = $lectureId;
    }

    public function collection()
    {
        $query = Attendance::with(['student.group.stage', 'lecture.subject']);
        
        if ($this->lectureId) {
            $query->where('lecture_id', $this->lectureId);
        }

        // If user is a teacher, restrict export to their own lectures
        if (auth()->check() && auth()->user()->hasRole('teacher')) {
            $query->whereHas('lecture', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        return $query->get();
    }

    public function map($attendance): array
    {
        $statusAr = match ($attendance->status) {
            'present' => 'حاضر',
            'absent' => 'غائب',
            'excused' => 'مجاز',
            default => 'غير معروف',
        };

        return [
            $attendance->student->first_name . ' ' . $attendance->student->last_name,
            $attendance->student->student_external_id ?? 'غير متوفر',
            $attendance->lecture->subject->name ?? 'غير متوفر',
            $attendance->lecture->date ? \Carbon\Carbon::parse($attendance->lecture->date)->format('Y-m-d') : 'غير متوفر',
            $attendance->lecture->time ? \Carbon\Carbon::parse($attendance->lecture->time)->format('H:i') : 'غير متوفر',
            $statusAr,
            $attendance->scanned_at ? \Carbon\Carbon::parse($attendance->scanned_at)->format('H:i:s') : 'بواسطة النظام',
        ];
    }

    public function headings(): array
    {
        return [
            'اسم الطالب',
            'الرقم الجامعي',
            'المادة',
            'تاريخ المحاضرة',
            'وقت المحاضرة',
            'حالة الحضور',
            'وقت التسجيل الفعلي',
        ];
    }
}


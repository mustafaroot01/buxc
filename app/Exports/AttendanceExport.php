<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        // Handle legacy call where $filters might be just a string (lectureId)
        if (is_string($filters)) {
            $this->filters = ['lecture_id' => $filters];
        } else {
            $this->filters = $filters;
        }
    }

    public function query()
    {
        $query = Attendance::query()
            ->with(['student.group.stage', 'lecture.subject']);

        // 1. Lecture Filter
        if (!empty($this->filters['lecture_id'])) {
            $query->where('lecture_id', $this->filters['lecture_id']);
        }

        // 2. Date Range
        if (!empty($this->filters['start_date'])) {
            $query->whereHas('lecture', function ($q) {
                $q->whereDate('start_time', '>=', $this->filters['start_date']);
            });
        }
        if (!empty($this->filters['end_date'])) {
            $query->whereHas('lecture', function ($q) {
                $q->whereDate('start_time', '<=', $this->filters['end_date']);
            });
        }

        // 3. Stage
        if (!empty($this->filters['stage_id'])) {
            $query->whereHas('student.group', function ($q) {
                $q->where('stage_id', $this->filters['stage_id']);
            });
        }

        // 4. Study Type
        if (!empty($this->filters['study_type'])) {
            $query->whereHas('student.group', function ($q) {
                $q->where('study_type', $this->filters['study_type']);
            });
        }

        // 5. Subject
        if (!empty($this->filters['subject_id'])) {
            $query->whereHas('lecture', function ($q) {
                $q->where('subject_id', $this->filters['subject_id']);
            });
        }

        // 6. Group
        if (!empty($this->filters['group_id'])) {
            $query->whereHas('student', function ($q) {
                $q->where('group_id', $this->filters['group_id']);
            });
        }

        // Role restriction
        if (auth()->check() && auth()->user()->hasRole('teacher')) {
            $query->whereHas('lecture', function ($q) {
                $q->where('teacher_id', auth()->id());
            });
        }

        return $query->orderBy('created_at', 'desc');
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
            $attendance->student->student_external_id ?? '---',
            $attendance->student->group->stage->name ?? '---',
            ($attendance->student->group->study_type ?? '') === 'morning' ? 'صباحي' : 'مسائي',
            $attendance->student->group->name ?? '---',
            $attendance->lecture->subject->name ?? '---',
            $attendance->lecture->date ? Carbon::parse($attendance->lecture->date)->format('Y-m-d') : '---',
            $attendance->check_in_at ? $attendance->check_in_at->format('H:i:s') : 'بواسطة النظام',
            $statusAr,
        ];
    }

    public function headings(): array
    {
        return [
            ['سجل حضور وانضباط الطلبة - نظام الحضور الذكي'],
            [], // Empty row for spacing
            [
                'اسم الطالب',
                'الرقم الجامعي',
                'المرحلة',
                'نوع الدراسة',
                'المجموعة/الكروب',
                'المادة',
                'تاريخ المحاضرة',
                'وقت الحضور الفعلي',
                'حالة الحضور',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge title row
        $sheet->mergeCells('A1:I1');
        
        return [
            // Title Style
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            // Header Style
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D9488']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
            // All Data Cells
            'A:I' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                
                // Set Row Heights
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(3)->setRowHeight(30);
                
                $highestRow = $sheet->getHighestRow();
                for ($row = 4; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                    
                    // Conditional Color for Status (Column I)
                    $statusValue = $sheet->getCell('I' . $row)->getValue();
                    if ($statusValue === 'حاضر') {
                        $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('059669'); // Emerald-600
                        $sheet->getStyle('I' . $row)->getFont()->setBold(true);
                    } elseif ($statusValue === 'غائب') {
                        $sheet->getStyle('I' . $row)->getFont()->getColor()->setRGB('DC2626'); // Red-600
                        $sheet->getStyle('I' . $row)->getFont()->setBold(true);
                    }
                }

                // Borders for all data
                $sheet->getStyle('A3:I' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sheet->getStyle('A3:I' . $highestRow)->getBorders()->getAllBorders()->getColor()->setRGB('E2E8F0');
            },
        ];
    }
}


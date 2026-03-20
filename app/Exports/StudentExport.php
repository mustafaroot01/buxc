<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Student::query()->with('group.stage');

        if (!empty($this->filters['stage_id'])) {
            $query->whereHas('group', function ($q) {
                $q->where('stage_id', $this->filters['stage_id']);
            });
        }

        if (!empty($this->filters['group_id'])) {
            $query->where('group_id', $this->filters['group_id']);
        }

        if (!empty($this->filters['study_type'])) {
            $query->whereHas('group', function ($q) {
                $q->where('study_type', $this->filters['study_type']);
            });
        }

        return $query->orderBy('first_name', 'asc');
    }

    public function headings(): array
    {
        return [
            ['قائمة أسماء الطلاب - نظام الحضور والغياب الذكي'],
            [],
            [
                'الاسم الكامل',
                'الرقم الجامعي',
                'الجنس',
                'المرحلة',
                'المجموعة/الكروب',
                'نوع الدراسة',
                'تاريخ الإضافة',
            ]
        ];
    }

    public function map($student): array
    {
        $fullName = trim($student->first_name . ' ' . $student->second_name . ' ' . $student->last_name);
        
        return [
            $fullName,
            $student->student_external_id,
            $student->gender === 'male' ? 'ذكر' : 'أنثى',
            $student->group->stage->name ?? '---',
            $student->group->name ?? '---',
            $student->group->study_type === 'morning' ? 'صباحي' : 'مسائي',
            $student->created_at->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:G1');
        
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D9488']
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
                
                $sheet->getRowDimension(1)->setRowHeight(35);
                $sheet->getRowDimension(3)->setRowHeight(25);
                
                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('A3:G' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            },
        ];
    }
}

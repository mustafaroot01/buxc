<?php

namespace App\Exports;

use App\Models\Lecture;
use App\Models\Student;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Maatwebsite\Excel\Events\AfterSheet;

class LectureAttendanceExport implements FromArray, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected Lecture $lecture;
    protected array $rows;
    protected int $headerRows = 7; // Number of info rows before the table

    public function __construct(string $lectureId)
    {
        $this->lecture = Lecture::with([
            'subject',
            'group.stage',
            'teacher',
            'attendances',
        ])->findOrFail($lectureId);

        $this->buildRows();
    }

    protected function buildRows(): void
    {
        $lecture = $this->lecture;

        // Get all students in the group
        $students = Student::where('group_id', $lecture->group_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'student_external_id']);

        // Attendance keyed by student_id
        $attendances = Attendance::where('lecture_id', $lecture->id)
            ->get()
            ->keyBy('student_id');

        $statusMap = [
            'present' => 'حاضر',
            'absent'  => 'غائب',
        ];

        $methodMap = [
            'qr'     => 'ماسح QR',
            'manual' => 'يدوي',
            null     => '—',
        ];

        $this->rows = $students->map(function ($student, $index) use ($attendances, $statusMap, $methodMap, $lecture) {
            $attendance = $attendances->get($student->id);
            $status = $attendance ? 'present' : 'absent';
            $method = $attendance ? $attendance->check_in_method : null;
            $checkInAt = $attendance && $attendance->check_in_at
                ? \Carbon\Carbon::parse($attendance->check_in_at)->format('H:i:s')
                : '—';

            return [
                'number' => $index + 1,
                'name'   => $student->first_name . ' ' . $student->last_name,
                'id'     => $student->student_external_id ?? '—',
                'status' => $statusMap[$status],
                'method' => $methodMap[$method],
                'time'   => $checkInAt,
            ];
        })->values()->toArray();
    }

    public function array(): array
    {
        $lecture = $this->lecture;
        $studyTypeAr = $lecture->group?->study_type === 'morning' ? 'صباحي' : 'مسائي';
        
        $rows = [
            ['تقرير حضور المحاضرة التفصيلي - نظام الحضور الذكي'],
            [],
            ['اسم المحاضرة:', $lecture->title, '', 'المادة:', $lecture->subject?->name ?? '---', ''],
            ['المجموعة:', $lecture->group?->name ?? '---', '', 'نوع الدراسة:', $studyTypeAr, ''],
            ['التاريخ:', $lecture->date, '', 'الوقت:', $lecture->time, ''],
            [],
            ['#', 'اسم الطالب', 'الرقم الجامعي', 'الحالة', 'طريقة التحضير', 'وقت المسح'],
        ];

        foreach ($this->rows as $row) {
            $rows[] = [
                $row['number'],
                $row['name'],
                $row['id'],
                $row['status'],
                $row['method'],
                $row['time'],
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('B3:C3');
        $sheet->mergeCells('E3:F3');
        $sheet->mergeCells('B4:C4');
        $sheet->mergeCells('E4:F4');
        $sheet->mergeCells('B5:C5');
        $sheet->mergeCells('E5:F5');

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            7 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A:F' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(7)->setRowHeight(30);
                
                $highestRow = $sheet->getHighestRow();
                for ($row = 8; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(25);
                    
                    $statusValue = $sheet->getCell('D' . $row)->getValue();
                    if ($statusValue === 'حاضر') {
                        $sheet->getStyle('D' . $row)->getFont()->getColor()->setRGB('059669');
                        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
                    } elseif ($statusValue === 'غائب') {
                        $sheet->getStyle('D' . $row)->getFont()->getColor()->setRGB('DC2626');
                        $sheet->getStyle('D' . $row)->getFont()->setBold(true);
                    }
                }

                $sheet->getStyle('A7:F' . $highestRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('A7:F' . $highestRow)->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);
            },
        ];
    }
}

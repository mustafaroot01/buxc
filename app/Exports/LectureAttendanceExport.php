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

        $startTime = $lecture->start_time
            ? \Carbon\Carbon::parse($lecture->start_time)
            : null;

        $studyTypeAr = $lecture->group?->study_type === 'morning' ? 'صباحي' : 'مسائي';
        $statusAr = $lecture->status === 'active' ? 'نشطة' : 'مغلقة';

        // === Info Header Block ===
        $rows = [
            ['كشف الحضور والغياب', '', '', '', '', ''],
            [''],
            ['اسم المحاضرة:', $lecture->title, '', 'الأستاذ:', $lecture->teacher?->full_name ?? '—', ''],
            ['المادة الدراسية:', $lecture->subject?->name . ' (' . $lecture->subject?->code . ')', '', 'المجموعة:', $lecture->group?->name . ' — ' . $studyTypeAr, ''],
            ['المرحلة:', $lecture->group?->stage?->name ?? '—', '', 'نوع المحاضرة:', "حالة المحاضرة: {$statusAr}", ''],
            ['التاريخ:', $startTime ? $startTime->format('Y-m-d') : '—', '', 'وقت البدء:', $startTime ? $startTime->format('H:i') : '—', ''],
            [''],
            // Table headings
            ['#', 'اسم الطالب', 'الرقم الجامعي', 'الحالة', 'طريقة التسجيل', 'وقت الدخول'],
        ];

        // Student data rows
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

    public function title(): string
    {
        return 'كشف الحضور';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 30,
            'C' => 18,
            'D' => 12,
            'E' => 18,
            'F' => 14,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalStudents = count($this->rows);
                $lastRow = $this->headerRows + 1 + $totalStudents; // +1 for header row

                // === RTL direction ===
                $sheet->setRightToLeft(true);

                // === Main Title Row 1 ===
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', 'كشف الحضور والغياب');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'name' => 'Arial',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0D9488'], // teal-600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(35);

                // === Empty row 2 ===
                $sheet->getRowDimension(2)->setRowHeight(5);

                // === Info rows (3-6) ===
                $infoLabelStyle = [
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F766E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF0FDFA']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
                $infoValueStyle = [
                    'font' => ['bold' => false, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                ];

                foreach ([3, 4, 5, 6] as $row) {
                    $sheet->getStyle("A{$row}")->applyFromArray($infoLabelStyle);
                    $sheet->getStyle("B{$row}")->applyFromArray($infoValueStyle);
                    $sheet->getStyle("D{$row}")->applyFromArray($infoLabelStyle);
                    $sheet->getStyle("E{$row}")->applyFromArray($infoValueStyle);
                    $sheet->getRowDimension($row)->setRowHeight(20);
                    $sheet->mergeCells("B{$row}:C{$row}");
                    $sheet->mergeCells("E{$row}:F{$row}");
                }

                // === Empty separator row 7 ===
                $sheet->getRowDimension(7)->setRowHeight(5);

                // === Table Header Row 8 ===
                $headerRow = $this->headerRows + 1;
                $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0D9488'], // teal-600
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF0F766E'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($headerRow)->setRowHeight(24);

                // === Student Data Rows ===
                $dataStartRow = $headerRow + 1;
                for ($i = 0; $i < $totalStudents; $i++) {
                    $row = $dataStartRow + $i;
                    $studentData = $this->rows[$i];
                    $isPresent = $studentData['status'] === 'حاضر';
                    $isEven = ($i % 2 === 0);

                    // Background: alternating rows
                    $bgColor = $isEven ? 'FFF9FAFB' : 'FFFFFFFF';

                    $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                        'font' => ['size' => 10],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => $bgColor],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical'   => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFE5E7EB'],
                            ],
                        ],
                    ]);

                    // Status cell color
                    $statusCell = "D{$row}";
                    if ($isPresent) {
                        $sheet->getStyle($statusCell)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF065F46']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD1FAE5']],
                        ]);
                    } else {
                        $sheet->getStyle($statusCell)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF991B1B']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                        ]);
                    }

                    // Name column left-aligned
                    $sheet->getStyle("B{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getRowDimension($row)->setRowHeight(19);
                }

                // === Summary Footer ===
                $footerRow = $lastRow + 1;
                $presentCount = collect($this->rows)->where('status', 'حاضر')->count();
                $absentCount = count($this->rows) - $presentCount;

                $sheet->mergeCells("A{$footerRow}:B{$footerRow}");
                $sheet->setCellValue("A{$footerRow}", "إجمالي: " . count($this->rows) . " طالب");
                $sheet->mergeCells("C{$footerRow}:D{$footerRow}");
                $sheet->setCellValue("C{$footerRow}", "حاضر: {$presentCount}");
                $sheet->mergeCells("E{$footerRow}:F{$footerRow}");
                $sheet->setCellValue("E{$footerRow}", "غائب: {$absentCount}");

                $sheet->getStyle("A{$footerRow}:F{$footerRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FF0F766E']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFCCFBF1']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0D9488']]],
                ]);
                $sheet->getRowDimension($footerRow)->setRowHeight(22);

                // Outline border around entire data table
                $sheet->getStyle("A{$headerRow}:F{$lastRow}")->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['argb' => 'FF0D9488'],
                        ],
                    ],
                ]);
            },
        ];
    }
}

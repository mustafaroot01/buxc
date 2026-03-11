<?php

namespace App\Exports;

use App\Models\Attendance;
use App\Models\Lecture;
use App\Models\Student;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LectureAttendanceExport implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    protected Lecture $lecture;
    protected array $rows;

    // Layout constants — easy to update
    const TABLE_START_ROW = 9; // Row 9 = table header (1 title + 1 empty + 3 info + 1 divider + 1 empty + 1 header = 9)

    public function __construct(string $lectureId)
    {
        $this->lecture = Lecture::with([
            'subject',
            'group.stage',
            'teacher',
        ])->findOrFail($lectureId);

        $this->buildRows();
    }

    protected function buildRows(): void
    {
        $lecture = $this->lecture;

        $students = Student::where('group_id', $lecture->group_id)
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'second_name', 'last_name', 'student_external_id']);

        $attendances = Attendance::where('lecture_id', $lecture->id)
            ->get()
            ->keyBy('student_id');

        $statusMap = ['present' => 'حاضر', 'absent' => 'غائب'];
        $methodMap = ['qr' => 'ماسح QR', 'manual' => 'يدوي', null => '—'];

        $this->rows = $students->map(function ($student, $index) use ($attendances, $statusMap, $methodMap) {
            $att    = $attendances->get($student->id);
            $status = $att ? ($att->status ?? 'present') : 'absent';
            $method = $att ? $att->check_in_method : null;
            $time   = $att && $att->check_in_at
                ? Carbon::parse($att->check_in_at)->format('H:i:s')
                : '—';

            return [
                $index + 1,
                trim($student->first_name . ' ' . $student->second_name . ' ' . $student->last_name),
                $student->student_external_id ?? '—',
                $statusMap[$status] ?? 'غائب',
                $methodMap[$method],
                $time,
            ];
        })->values()->toArray();
    }

    /**
     * Pure 6-column grid:
     *   Col A  | Col B    | Col C | Col D      | Col E | Col F
     * Row 1:  Title (merged A1:F1)
     * Row 2:  empty
     * Row 3:  اسم المحاضرة: | value(B:C) | المادة:      | value(E:F)
     * Row 4:  المجموعة:     | value(B:C) | نوع الدراسة: | value(E:F)
     * Row 5:  التاريخ:      | value(B:C) | الوقت:       | value(E:F)
     * Row 6:  الأستاذ:      | value(B:C) | المرحلة:     | value(E:F)
     * Row 7:  divider (empty, styled)
     * Row 8:  empty
     * Row 9:  # | الطالب | الرقم | الحالة | الطريقة | الوقت
     * Row 10+: data
     */
    public function array(): array
    {
        $l = $this->lecture;
        $studyTypeAr = $l->group?->study_type === 'morning' ? 'صباحي' : 'مسائي';
        $teacherName = $l->teacher?->name ?? '—';
        $stageName   = $l->group?->stage?->name ?? '—';

        $header = [
            // Row 1 — Title
            ['تقرير حضور المحاضرة التفصيلي - نظام الحضور الذكي', '', '', '', '', ''],
            // Row 2 — empty spacer
            ['', '', '', '', '', ''],
            // Row 3
            ['اسم المحاضرة:', $l->title, '', 'المادة:', $l->subject?->name ?? '---', ''],
            // Row 4
            ['المجموعة:', $l->group?->name ?? '---', '', 'نوع الدراسة:', $studyTypeAr, ''],
            // Row 5
            ['التاريخ:', $l->date ?? '', '', 'الوقت:', $l->time ?? '', ''],
            // Row 6
            ['الأستاذ:', $teacherName, '', 'المرحلة:', $stageName, ''],
            // Row 7 — divider
            ['', '', '', '', '', ''],
            // Row 8 — empty spacer
            ['', '', '', '', '', ''],
            // Row 9 — Table header
            ['#', 'اسم الطالب الكامل', 'الرقم الجامعي', 'حالة الحضور', 'طريقة التحضير', 'وقت التسجيل'],
        ];

        return array_merge($header, $this->rows);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 7,   // #  / label
            'B' => 30,  // Name / value
            'C' => 5,   // spacer
            'D' => 16,  // Status / label
            'E' => 16,  // Method / value
            'F' => 14,  // Time
        ];
    }

    public function title(): string
    {
        return 'تقرير الحضور - ' . $this->lecture->subject?->name;
    }

    public function styles(Worksheet $sheet): array
    {
        $TR = self::TABLE_START_ROW; // 9

        // ── Merges ─────────────────────────────────────────────────────────
        $sheet->mergeCells("A1:F1");       // title

        foreach ([3, 4, 5, 6] as $row) {
            $sheet->mergeCells("B{$row}:C{$row}"); // left value
            $sheet->mergeCells("E{$row}:F{$row}"); // right value
        }

        $sheet->mergeCells("A7:F7");  // divider row
        $sheet->mergeCells("A8:F8"); // spacer row

        // ── Base alignment ───────────────────────────────────────────────
        $sheet->getStyle("A1:F{$TR}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Right-align info labels & values
        $sheet->getStyle('A3:A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('D3:D6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('B3:C6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E3:F6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        return [
            // Title
            1 => [
                'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],

            // Info labels column A
            'A3:A6' => [
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '374151']],
            ],

            // Info labels column D
            'D3:D6' => [
                'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '374151']],
            ],

            // Info values
            'B3:C6' => ['font' => ['size' => 10]],
            'E3:F6' => ['font' => ['size' => 10]],

            // Divider row 7 — teal background
            7 => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D9488']],
            ],

            // Table header row 9
            $TR => [
                'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $TR    = self::TABLE_START_ROW;

                $sheet->setRightToLeft(true);

                // ── Row heights ──────────────────────────────────────────
                $sheet->getRowDimension(1)->setRowHeight(45);
                $sheet->getRowDimension(7)->setRowHeight(6);   // divider stripe
                $sheet->getRowDimension(8)->setRowHeight(8);   // spacer
                $sheet->getRowDimension($TR)->setRowHeight(28);

                foreach ([2, 3, 4, 5, 6] as $r) {
                    $sheet->getRowDimension($r)->setRowHeight(22);
                }

                // ── Student rows ─────────────────────────────────────────
                $highestRow = $sheet->getHighestRow();
                for ($i = $TR + 1; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(22);

                    // Alternating zebra stripe
                    if (($i - $TR) % 2 === 0) {
                        $sheet->getStyle("A{$i}:F{$i}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('F0FDFA');
                    }

                    // Colour-code status (col D)
                    $status = $sheet->getCell("D{$i}")->getValue();
                    $color  = ($status === 'حاضر') ? '059669' : 'DC2626';
                    $sheet->getStyle("D{$i}")->getFont()->getColor()->setRGB($color);
                    $sheet->getStyle("D{$i}")->getFont()->setBold(true);
                }

                // ── Thin borders for table area only ─────────────────────
                $tableRange = "A{$TR}:F{$highestRow}";
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('D1D5DB');
                $sheet->getStyle($tableRange)->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('0D9488');

                // ── Info section: light box around rows 3-6 ──────────────
                $sheet->getStyle('A3:F6')->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('E5E7EB');

                // ── Column C width to act as pure spacer (collapse it) ────
                $sheet->getColumnDimension('C')->setWidth(3);
            },
        ];
    }
}

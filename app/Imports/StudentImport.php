<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Str;

class StudentImport implements ToModel, WithHeadingRow
{
    use Importable;

    protected $groupId;
    protected $studyType;

    public function __construct($groupId, $studyType)
    {
        $this->groupId = $groupId;
        $this->studyType = $studyType;
    }

    public function model(array $row)
    {
        // Skip if mandatory fields are missing
        if (empty($row['first_name']) || empty($row['student_external_id'])) {
            return null;
        }

        // Handle gender mapping
        $gender = 'male';
        if (isset($row['gender'])) {
            $g = trim(mb_strtolower($row['gender']));
            if ($g === 'female' || $g === 'أنثى' || $g === 'انثى') {
                $gender = 'female';
            }
        }

        return new Student([
            'first_name'          => $row['first_name'],
            'second_name'         => $row['second_name'] ?? '',
            'last_name'           => $row['last_name'] ?? '',
            'student_external_id' => $row['student_external_id'],
            'gender'              => $gender,
            'group_id'            => $this->groupId,
            'study_type'          => $this->studyType,
            'qr_payload'          => Str::random(24),
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrPrintController extends Controller
{
    public function index()
    {
        $stages = \App\Models\AcademicStage::with('groups')->get();
        return Inertia::render('Admin/Print/Index', [
            'stages' => $stages
        ]);
    }

    public function generate(Request $request)
    {
        $query = Student::with('group.stage');
        
        if ($request->has('ids')) {
            $ids = explode(',', $request->get('ids'));
            $query->whereIn('id', $ids);
        } else {
            if ($request->filled('stage_id')) {
                $query->whereHas('group', function ($q) use ($request) {
                    $q->where('stage_id', $request->get('stage_id'));
                });
            }
            if ($request->filled('group_id')) {
                $query->where('group_id', $request->get('group_id'));
            }
            if ($request->filled('study_type')) {
                $query->whereHas('group', function ($q) use ($request) {
                    $q->where('study_type', $request->get('study_type'));
                });
            }
        }

        $students = $query->get()->map(function($student) {
            // Generate Base64 SVG for printing directly in the view
            // Apply custom styling for a beautiful, distinct look (Deep purple on soft lilac, rounded dots)
            $qrSvg = QrCode::size(200)
                ->color(31, 22, 69) // Dark purple
                ->backgroundColor(248, 240, 255) // Soft pale lilac
                ->margin(2) // Some breathing room around the edges
                ->style('round') // Modern circular dots instead of harsh squares
                ->generate($student->qr_payload);
            
            return [
                'id' => $student->id,
                'name' => $student->full_name,
                'external_id' => $student->student_external_id,
                'group' => $student->group->name ?? '',
                'stage' => $student->group->stage->name ?? '',
                'qr_svg' => base64_encode($qrSvg)
            ];
        });

        return Inertia::render('Admin/Print/QrPage', [
            'students' => $students
        ]);
    }
}


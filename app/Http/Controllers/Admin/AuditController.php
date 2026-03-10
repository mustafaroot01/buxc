<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%");
        }

        $activities = $query->paginate(20);

        return Inertia::render('Admin/Audit/Index', [
            'activities' => $activities,
            'filters' => $request->only('search')
        ]);
    }
}


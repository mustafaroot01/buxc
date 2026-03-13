<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RegistrationFormController;
use App\Http\Controllers\Admin\WarningController as AdminWarningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\WarningController as TeacherWarningController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Admin Routes
    Route::middleware(['role:admin|super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Students Management
        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);

        // Core Management Modules
        Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
        Route::resource('stages', \App\Http\Controllers\Admin\AcademicStageController::class);
        Route::resource('groups', \App\Http\Controllers\Admin\AcademicGroupController::class);
        Route::resource('subjects', \App\Http\Controllers\Admin\SubjectController::class);

        // Lectures Monitor (read-only)
        Route::get('/lectures', [\App\Http\Controllers\Admin\LectureController::class, 'index'])->name('lectures.index');
        Route::get('/lectures/{id}/export', [\App\Http\Controllers\Admin\LectureController::class, 'export'])->name('lectures.export');

        // QR Print Center
        Route::get('/print-qrs', [\App\Http\Controllers\Admin\QrPrintController::class, 'index'])->name('print.qrs');
        Route::get('/print-qrs/generate', [\App\Http\Controllers\Admin\QrPrintController::class, 'generate'])->name('print.qrs.generate');

        // Settings
        Route::get('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

        // Archive Management Center
        Route::get('/archive', [\App\Http\Controllers\Admin\ArchiveController::class, 'index'])->name('archive.index');
        Route::post('/archive/{id}/restore', [\App\Http\Controllers\Admin\ArchiveController::class, 'restore'])->name('archive.restore');
        Route::delete('/archive/{id}', [\App\Http\Controllers\Admin\ArchiveController::class, 'destroy'])->name('archive.destroy');

        // Lectures Archive
        Route::get('/archives/lectures', [\App\Http\Controllers\Admin\LectureArchiveController::class, 'index'])->name('archives.lectures.index');
        Route::post('/archives/lectures/{id}/restore', [\App\Http\Controllers\Admin\LectureArchiveController::class, 'restore'])->name('archives.lectures.restore');
        Route::delete('/archives/lectures/{id}', [\App\Http\Controllers\Admin\LectureArchiveController::class, 'destroy'])->name('archives.lectures.destroy');

        // Audit Trail
        Route::get('/audit', [\App\Http\Controllers\Admin\AuditController::class, 'index'])->name('audit.index');

        // Reports & Exporting
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/download_export/{file}', [\App\Http\Controllers\Admin\ReportController::class, 'downloadExport'])->name('reports.download_export');

        // Warnings
        Route::get('/warnings', [AdminWarningController::class, 'index'])->name('warnings.index');
        Route::get('/warnings/export', [AdminWarningController::class, 'export'])->name('warnings.export');

        // Registration Forms (Super Admin only)
        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('/registrations', [RegistrationFormController::class, 'index'])->name('registrations.index');
            Route::post('/registrations', [RegistrationFormController::class, 'store'])->name('registrations.store');
            Route::post('/registrations/{id}/toggle', [RegistrationFormController::class, 'toggle'])->name('registrations.toggle');
            Route::get('/registrations/{id}/submissions', [RegistrationFormController::class, 'submissions'])->name('registrations.submissions');
            Route::post('/registrations/{id}/approve/{submissionId}', [RegistrationFormController::class, 'approve'])->name('registrations.approve');
            Route::post('/registrations/{id}/approve-all', [RegistrationFormController::class, 'approveAll'])->name('registrations.approve-all');
            Route::post('/registrations/{id}/reject/{submissionId}', [RegistrationFormController::class, 'reject'])->name('registrations.reject');
            Route::delete('/registrations/{id}', [RegistrationFormController::class, 'destroy'])->name('registrations.destroy');
        });
    });

    // Teacher Routes
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        // Lectures Management
        Route::get('/lectures', [\App\Http\Controllers\Teacher\LectureController::class, 'index'])->name('lectures.index');
        Route::get('/lectures/create', [\App\Http\Controllers\Teacher\LectureController::class, 'create'])->name('lectures.create');
        Route::post('/lectures', [\App\Http\Controllers\Teacher\LectureController::class, 'store'])->name('lectures.store');
        Route::get('/lectures/{id}', [\App\Http\Controllers\Teacher\LectureController::class, 'show'])->name('lectures.show');
        Route::get('/lectures/{id}/export', [\App\Http\Controllers\Teacher\LectureController::class, 'export'])->name('lectures.export');
        Route::post('/lectures/{lecture}/mark-manual', [\App\Http\Controllers\Teacher\LectureController::class, 'markManual'])->name('lectures.mark-manual');
        Route::delete('/lectures/{id}', [\App\Http\Controllers\Teacher\LectureController::class, 'destroy'])->name('lectures.destroy');

        // Live Scanner
        Route::get('/scanner/{lecture}', [\App\Http\Controllers\Teacher\ScannerController::class, 'show'])->name('scanner.show');
        Route::post('/scanner/{lecture}/scan', [\App\Http\Controllers\Teacher\ScannerController::class, 'store'])->name('scanner.store');
        Route::post('/scanner/{lecture}/close', [\App\Http\Controllers\Teacher\ScannerController::class, 'close'])->name('scanner.close');

        // Warnings Monitor
        Route::get('/warnings', [TeacherWarningController::class, 'index'])->name('warnings.index');
    });

    // Fallback Dashboard Route for generic redirection
    Route::get('/dashboard', function () {
        if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('teacher.dashboard');
    })->name('dashboard');

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Public Registration Routes (No Auth Required)
Route::get('/register/{slug}', [RegistrationController::class, 'show'])->name('registration.show');
Route::post('/register/{slug}', [RegistrationController::class, 'submit'])->name('registration.submit');
Route::get('/student-lookup', [RegistrationController::class, 'lookup'])->name('registration.lookup');

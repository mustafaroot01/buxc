<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Attendance;
use App\Models\AttendanceSyncLog;
use App\Models\Lecture;
use App\Models\Student;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceSyncController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $startTime = microtime(true);
        
        $validated = $request->validate([
            'sync_id' => 'required|string|unique:attendance_sync_logs,sync_id',
            'lecture_id' => 'required|string', // Relaxed validation to allow offline_ IDs
            'device_info' => 'required|array',
            'device_info.id' => 'required|string',
            'device_info.model' => 'nullable|string',
            'device_info.os_version' => 'nullable|string',
            'device_info.app_version' => 'nullable|string',
            'sent_at' => 'required|date',
            'action_type' => 'nullable|string|in:scan,manual', 
            'scans' => 'required|array',
            'scans.*.student_id' => 'required|string', // Relaxed validation
            'scans.*.scanned_at' => 'required|date',
            'scans.*.request_id' => 'required|string', 
        ]);

        // Resolve Lecture: Try ID first, then offline_id
        $lecture = Lecture::where('id', $request->lecture_id)
            ->orWhere('offline_id', $request->lecture_id)
            ->first();

        if (!$lecture) {
            return $this->error('المحاضرة غير موجودة، يرجى التأكد من مزامنة المحاضرات أولاً.', 404);
        }

        $scansCount = count($request->scans);
        $processedCount = 0;
        $failedCount = 0;
        $studentIdsToProcess = [];

        // Device Registry Update
        try {
            \App\Models\Device::updateOrCreate(
                ['device_id' => $request->device_info['id']],
                [
                    'model' => $request->device_info['model'] ?? null,
                    'os_version' => $request->device_info['os_version'] ?? null,
                    'app_version' => $request->device_info['app_version'] ?? null,
                    'last_seen_at' => now(),
                ]
            );
        } catch (\Exception $e) {
            Log::warning("Failed to update device registry: " . $e->getMessage());
        }

        try {
            // 1. Pre-fetch Data for Batching
            $scans = $request->scans;
            $studentIds = array_unique(array_column($scans, 'student_id'));
            $requestIds = array_map(function ($scan) {
                return pack('H*', str_replace('-', '', $scan['request_id']));
            }, $scans);

            // Pre-fetch Students (Search by UUID or offline_id)
            $studentsMap = Student::where(function($q) use ($studentIds) {
                    $q->whereIn('id', $studentIds)
                      ->orWhereIn('offline_id', $studentIds);
                })
                ->select(['id', 'offline_id', 'first_name', 'second_name', 'last_name', 'group_id'])
                ->get();
            
            // Build a map that handles both true IDs and offline IDs
            $studentsLookup = [];
            foreach ($studentsMap as $s) {
                $studentsLookup[$s->id] = $s;
                if ($s->offline_id) {
                    $studentsLookup[$s->offline_id] = $s;
                }
            }

            // Pre-fetch existing Attendances for this lecture/students
            $resolvedStudentIds = $studentsMap->pluck('id')->toArray();
            $existingAttendancesMap = Attendance::withTrashed()
                ->where('lecture_id', $lecture->id)
                ->whereIn('student_id', $resolvedStudentIds)
                ->get()
                ->keyBy('student_id');

            // Pre-fetch global request_ids (idempotency check)
            $globalRequestIdsSet = array_flip(array_map('bin2hex', Attendance::whereIn('request_id', $requestIds)
                ->pluck('request_id', 'id')
                ->toArray()));

            $errorDetails = [];
            DB::beginTransaction();

            foreach ($scans as $scanData) {
                $rawRequestId = str_replace('-', '', $scanData['request_id']);
                $requestIdBinary = pack('H*', $rawRequestId);

                // Idempotency: Skip if request_id already exists globally
                if (isset($globalRequestIdsSet[$rawRequestId])) {
                    $processedCount++;
                    $studentIdsToProcess[] = $scanData['student_id'];
                    continue;
                }

                $student = $studentsLookup[$scanData['student_id']] ?? null;
                if (!$student) {
                    $failedCount++;
                    $errorDetails[] = [
                        'student_id' => $scanData['student_id'],
                        'error' => 'Student not found',
                        'request_id' => $scanData['request_id']
                    ];
                    continue;
                }

                $scannedAt = Carbon::parse($scanData['scanned_at']);
                $attendance = $existingAttendancesMap->get($student->id);

                if ($attendance) {
                    $shouldUpdate = false;

                    if ($attendance->trashed()) {
                        $attendance->restore();
                        $shouldUpdate = true;
                    }

                    $currentScanTime = $attendance->scanned_at ?? $attendance->check_in_at;
                    if ($scannedAt->lt($currentScanTime)) {
                        $shouldUpdate = true;
                    }

                    if ($shouldUpdate) {
                        $attendance->update([
                            'status' => 'present',
                            'scanned_at' => $scannedAt,
                            'check_in_at' => $scannedAt,
                            'device_id' => $request->device_info['id'],
                            'request_id' => $requestIdBinary,
                            'check_in_method' => 'sync',
                        ]);
                    }
                } else {
                    Attendance::create([
                        'lecture_id' => $lecture->id,
                        'student_id' => $student->id,
                        'status' => 'present',
                        'scanned_at' => $scannedAt,
                        'check_in_at' => $scannedAt,
                        'device_id' => $request->device_info['id'],
                        'request_id' => $requestIdBinary,
                        'check_in_method' => 'sync',
                    ]);
                }

                $studentIdsToProcess[] = $student->id;
                $processedCount++;
            }

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            // Log the sync operation
            AttendanceSyncLog::create([
                'sync_id' => $request->sync_id,
                'device_id' => $request->device_info['id'],
                'device_model' => $request->device_info['model'] ?? null,
                'os_version' => $request->device_info['os_version'] ?? null,
                'app_version' => $request->device_info['app_version'] ?? null,
                'lecture_id' => $lecture->id,
                'action_type' => $request->action_type ?? AttendanceSyncLog::ACTION_SCAN,
                'scans_received' => $scansCount,
                'scans_processed' => $processedCount,
                'failed_scans' => $failedCount,
                'sent_at' => Carbon::parse($request->sent_at),
                'synced_at' => now(),
                'duration_ms' => $durationMs,
                'status' => $failedCount === 0 
                    ? AttendanceSyncLog::STATUS_SUCCESS 
                    : ($processedCount > 0 ? AttendanceSyncLog::STATUS_PARTIAL : AttendanceSyncLog::STATUS_FAILED),
                'error_details' => !empty($errorDetails) ? $errorDetails : null,
            ]);

            // Dispatch post-sync tasks (streaks and warnings)
            if (!empty($studentIdsToProcess)) {
                \App\Jobs\ProcessSyncPostTasks::dispatch($studentIdsToProcess);
            }

            DB::commit();

            return $this->success([
                'received' => $scansCount,
                'processed' => $processedCount,
                'failed' => $failedCount,
                'sync_id' => $request->sync_id,
                'errors' => $errorDetails,
            ], 'تمت عملية المزامنة بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();

            // Sanitize the exception message for logging
            $message = $e->getMessage();
            if (!mb_check_encoding($message, 'UTF-8')) {
                $message = mb_convert_encoding($message, 'UTF-8', 'UTF-8');
            }

            Log::error("Attendance Sync Failed: " . $message, [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 1000), // Limit trace size
            ]);

            return $this->error('حدث خطأ أثناء المزامنة: ' . $message, 500);
        }
    }
}

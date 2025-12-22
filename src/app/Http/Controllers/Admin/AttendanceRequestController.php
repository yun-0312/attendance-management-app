<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceRequestController extends Controller
{
    public function list () {
        $pending = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'pending')
            ->orderBy('requested_clock_in')
            ->paginate(9);
        $approved = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'approved')
            ->orderBy('requested_clock_in')
            ->paginate(9);
        return view('admin.request.list', compact('pending', 'approved'));
    }

    public function show (AttendanceRequest $attendanceRequest)
    {
        $attendance = $attendanceRequest->attendance;
        $breaks = $attendanceRequest->breakTimeRequests()->orderBy('requested_break_start')->get();
        return view('admin.request.approve', compact('attendance', 'breaks', 'attendanceRequest'));
    }

    public function approve (AttendanceRequest $attendanceRequest) {
        if (!auth('admin')->check()) {
            abort(403);
        }

        DB::transaction(function () use ($attendanceRequest) {
            if ($attendanceRequest->attendance_id === null) {
                $attendance = Attendance::create([
                    'user_id'   => $attendanceRequest->user_id,
                    'work_date' => $attendanceRequest->work_date,
                    'clock_in'  => $attendanceRequest->requested_clock_in,
                    'clock_out' => $attendanceRequest->requested_clock_out,
                ]);

                // attendance_id を紐付け
                $attendanceRequest->update([
                    'attendance_id' => $attendance->id,
                    'status' => 'approved',
                    'approved_by' => auth('admin')->id(),
                ]);
            } else {
                $attendance = $attendanceRequest->attendance;
                $attendanceRequest->update([
                    'status' => 'approved',
                    'approved_by' => auth('admin')->id(),
                ]);
                $attendance->update([
                    'clock_in'  => $attendanceRequest->requested_clock_in,
                    'clock_out' => $attendanceRequest->requested_clock_out,
                ]);
            }
            $attendance->breakTimes()->delete();
            foreach ($attendanceRequest->breakTimeRequests as $reqBreak) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $reqBreak->requested_break_start,
                    'break_end'     => $reqBreak->requested_break_end,
                ]);
            }
        });
        return redirect()
            ->route('attendance_request.list')
            ->with('success', '修正申請を承認しました');
    }
}
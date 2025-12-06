<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTime;
use App\Models\BreakTimeRequest;
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
        DB::transaction(function () use ($attendanceRequest) {
            $attendance = $attendanceRequest->attendance;
            $attendanceRequest->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
            ]);
            $attendance->update([
                'clock_in'  => $attendanceRequest->requested_clock_in,
                'clock_out' => $attendanceRequest->requested_clock_out,
            ]);
            foreach ($attendanceRequest->breakTimeRequests as $reqBreak) {
                if ($reqBreak->break_time_id) {
                    BreakTime::where('id', $reqBreak->break_time_id)
                        ->update([
                            'break_start' => $reqBreak->requested_break_start,
                            'break_end'   => $reqBreak->requested_break_end,
                        ]);
                } else {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start'   => $reqBreak->requested_break_start,
                        'break_end'     => $reqBreak->requested_break_end,
                    ]);
                }
            }
        });
        return redirect()
            ->route('attendance.request.list')
            ->with('success', '修正申請を承認しました');
    }
}

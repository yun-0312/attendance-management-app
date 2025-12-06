<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function list () {
        $pending = AttendanceRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->where('status', 'pending')
            ->orderBy('requested_clock_in')
            ->paginate(9);
        $approved = AttendanceRequest::with(['user', 'attendance'])
            ->where('user_id', auth()->id())
            ->where('status', 'approved')
            ->orderBy('requested_clock_in')
            ->paginate(9);
        return view('user.request.list', compact('pending', 'approved'));
    }

    public function detail (AttendanceRequest $attendanceRequest) {
        if ($attendanceRequest->user_id !== auth()->id()) {
            abort(403);
        }
        $attendance = $attendanceRequest->attendance;
        $breaks = $attendanceRequest->breakTimeRequests()->orderBy('requested_break_start')->get();
        return view('user.request.detail', compact('attendance', 'breaks', 'attendanceRequest'));
    }
}

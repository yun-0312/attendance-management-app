<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function list () {
        $pending = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        $approved = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.request.list', compact('pending', 'approved'));
    }

    public function detail(AttendanceRequest $attendanceRequest)
    {
        $attendance = $attendanceRequest->attendance;
        $breaks = $attendanceRequest->breakTimeRequests()->orderBy('requested_break_start')->get();
        return view('user.request.detail', compact('attendance', 'breaks', 'attendanceRequest'));
    }
}

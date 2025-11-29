<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRequest;
use Illuminate\Http\Request;

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
        $pendingRequest = $attendanceRequest;
        return view('user.attendance.detail', compact('attendance', 'breaks', 'pendingRequest', 'attendanceRequest'));
    }
}

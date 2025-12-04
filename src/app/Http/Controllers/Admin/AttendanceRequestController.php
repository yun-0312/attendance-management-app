<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

class AttendanceRequestController extends Controller
{
    public function list () {
        $pending = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'pending')
            ->orderBy('requested_clock_in')
            ->get();
        $attendanceRequest =         $approved = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'approved')
            ->orderBy('requested_clock_in')
            ->get();
        return view('admin.request.list', compact('pending', 'approved'));
    }

    public function approve(AttendanceRequest $attendanceRequest)
    {
        $attendance = $attendanceRequest->attendance;
        $breaks = $attendanceRequest->breakTimeRequests()->orderBy('requested_break_start')->get();
        return view('admin.request.approve', compact('attendance', 'breaks', 'attendanceRequest'));
    }
}

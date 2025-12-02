<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function list (Request $request) {
        $date = $request->input('date', Carbon::today()->toDateString());
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $date)
            ->orderBy('user_id')
            ->get();
        return view('admin.attendance.list', compact('attendances', 'date'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index () {   
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', today()->toDateString())
            ->first();
        return view('user.attendance.index', compact('attendance'));
    }

    public function start () {
        Attendance::create([
            'user_id' => auth()->id(),
            'work_date' => now()->toDateString(),
            'clock_in' => now(),
        ]);
        return redirect()->route('attendance.index')->with('success', '出勤しました');
    }

    public function end () {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        $attendance->update([
            'clock_out' => now(),
        ]);

        return back()->with('success', '退勤しました');
    }

    public function breakStart () {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        $attendance->breakTimes()->create([
            'break_start' => now(),
        ]);

        return back()->with('success', '休憩開始しました');
    }

    public function breakEnd () {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->first();

        $break = $attendance->breakTimes()->whereNull('break_end')->first();

        $break->update([
            'break_end' => now(),
        ]);

        return back()->with('success', '休憩終了しました');
    }
}

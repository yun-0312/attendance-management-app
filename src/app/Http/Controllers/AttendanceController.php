<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->with('breakTimes')
            ->latest()
            ->first();

        // ステータスをまとめて生成
        $current_status = $attendance->current_status ?? '勤務外';

        return view('user.attendance.index', compact('attendance', 'current_status'));
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

    public function list () {
        $attendances = Attendance::where('user_id', auth()->id())
            ->with('breakTimes')
            ->orderBy('work_date', 'desc')
            ->paginate(10);

        return view('user.attendance.list', compact('attendances'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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

    public function list (Request $request) {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $attendances = Attendance::forMonth(auth()->id(), $year, $month);

        $start = Attendance::monthDate($year, $month);
        $end   = $start->copy()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        $prevMonth = Attendance::prevMonth($year, $month);
        $nextMonth = Attendance::nextMonth($year, $month);

        return view('user.attendance.list', compact(
            'attendances','period','year','month','prevMonth','nextMonth'
        ));
    }
}

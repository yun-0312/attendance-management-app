<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\CarbonPeriod;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\AttendanceRequest;
use App\Models\BreakTimeRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index () {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('work_date', today())
            ->with('breakTimes')
            ->latest()
            ->first();
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

    public function detail (Attendance $attendance) {
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }
        $breaks = $attendance->breakTimes()->orderBy('break_start')->get();
        return view('user.attendance.detail', compact('attendance', 'breaks'));
    }

    public function update (UpdateAttendanceRequest $request, Attendance $attendance) {
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $date = $attendance->work_date->toDateString();

        // attendances は更新しない＝修正申請として保存
        $attendanceRequest = AttendanceRequest::create([
            'attendance_id'       => $attendance->id,
            'user_id'             => auth()->id(),
            'requested_clock_in'  => Carbon::parse("$date {$request->clock_in}"),
            'requested_clock_out' => Carbon::parse("$date {$request->clock_out}"),
            'reason'              => $request->reason,
            'status'              => 'pending',
        ]);

        // break_time_requests を全て作成
        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                // 休憩欄が空なら skip
                if (empty($break['start']) && empty($break['end'])) {
                    continue;
                }

                BreakTimeRequest::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'break_time_id'         => null, // 元BreakTimeと紐づけない場合
                    'requested_break_start' => isset($break['start']) ? Carbon::parse("$date {$break['start']}") : null,
                    'requested_break_end'   => isset($break['end']) ? Carbon::parse("$date {$break['end']}") : null,
                ]);
            }
        }

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('success', '修正申請を送信しました');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\CarbonPeriod;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\AttendanceRequest;
use App\Models\BreakTimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
        $pendingRequest = $attendance->latestPendingRequest();
        if ($pendingRequest) {
            return redirect()->route('attendanceRequest.detail', [
                'attendanceRequest' => $pendingRequest->id
            ]);
        }
        $breaks = $attendance->breakTimes()->orderBy('break_start')->get();
        return view('user.attendance.detail', compact('attendance', 'breaks'));
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }
        // 変更がなければリダイレクトだけする
        if (!$attendance->isChangedFromOriginal($request)) {
            return redirect()
                ->route('attendance.detail', $attendance->id)
                ->with('success', '変更がありませんでした');
        }
        // 変更があるときだけ Request を作る
        $attendance->createRequestFromUpdate($request);
        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('success', '修正申請を送信しました');
    }
}

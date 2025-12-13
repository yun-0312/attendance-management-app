<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTimeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Requests\UpdateAttendanceRequest;

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
        if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            $year = $date->year;
            $month = $date->month;
        } else {
            $year = $request->input('year', now()->year);
            $month = $request->input('month', now()->month);
            $date = Carbon::create($year, $month, 1);
        }
        $attendances = Attendance::forMonth(auth()->id(), $year, $month);
        $start = Attendance::monthDate($year, $month);
        $end   = $start->copy()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);
        $prevMonth = Attendance::prevMonth($year, $month);
        $nextMonth = Attendance::nextMonth($year, $month);

        return view('user.attendance.list', compact(
            'date', 'attendances','period','year','month','prevMonth','nextMonth'
        ));
    }

    public function detail (Request $request, $id) {
        $date = Carbon::parse($request->query('date'))->startOfDay();
        $userId = auth()->id();
        $pending = AttendanceRequest::where('user_id', $userId)
            ->whereDate('work_date', $date)
            ->where('status', 'pending')
            ->latest()
            ->first();
        if ($pending) {
            return redirect()->route('attendance_request.detail', [
                'attendanceRequest' => $pending->id,
            ]);;
        }

        if ((int)$id === 0) {
            // 新規日（attendance なし）
            $attendance = null;
            $breaks = collect();
        } else {
            $attendance = Attendance::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();
            if ($attendance->user_id !== auth()->id()) {
                abort(403);
            }
            $breaks = $attendance->breakTimes()->orderBy('break_start')->get();
        }
        return view('user.attendance.detail', compact('attendance', 'breaks','date'));
    }

    public function storeRequest(UpdateAttendanceRequest $request)
    {
        $user = auth()->user();
        $date = Carbon::parse($request->query('date'))->startOfDay();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $date)
            ->first();
        if ($attendance && !$attendance->isChangedFromOriginal($request)) {
            return redirect()
                ->route('attendance.detail', [
                    'id'   => $attendance->id,
                    'date' => $attendance->work_date->toDateString(),
                ])
                ->with('success', '変更がありませんでした');
        }
        $exists = AttendanceRequest::where('user_id', $user->id)
                ->whereDate('work_date', $date)
                ->where('status', 'pending')
                ->exists();
        if ($exists) {
                abort(409, '既に修正申請があります');
        }
        DB::transaction(function () use ($request, $user, $date, $attendance) {
            // AttendanceRequest 作成
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id' => $attendance?->id,
                'user_id' => $user->id,
                'work_date' => $date,
                'requested_clock_in' => Carbon::parse($date->toDateString() . ' ' . $request->clock_in),
                'requested_clock_out' => Carbon::parse($date->toDateString() . ' ' . $request->clock_out),
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            // BreakTimeRequest 作成
            foreach ($request->input('breaks', []) as $break) {
                if (empty($break['start']) || empty($break['end'])) {
                    continue;
                }
                BreakTimeRequest::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'requested_break_start' => Carbon::parse($date->toDateString() . ' ' . $break['start']),
                    'requested_break_end'   => Carbon::parse($date->toDateString() . ' ' . $break['end']),
                ]);
            }
        });
        return redirect()->route('attendance.detail', [
            'id' => 0,
            'date' => $date->toDateString(),
        ])->with('success', '修正申請を送信しました');
    }
}
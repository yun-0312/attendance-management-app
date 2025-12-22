<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\BreakTimeRequest;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Http\Requests\UpdateAttendanceRequest;

class AttendanceController extends Controller
{
    public function list (Request $request) {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();
        $attendances = Attendance::with('user', 'breakTimes')
            ->whereDate('work_date', $date)
            ->orderBy('user_id')
            ->get();
        return view('admin.attendance.list', compact('attendances', 'date'));
    }

    public function detail (Request $request, $id) {
        if (!auth('admin')->check()) {
            abort(403);
        }
        if ((int)$id === 0) {
            // 新規日（attendance なし）
            $attendance = null;
            $userId = $request->query('user_id');
            $dateParam = $request->query('date');
            if (!$userId || !$dateParam) {
                abort(404);
            }
            $user = User::findOrFail($userId);
            $date = Carbon::parse($dateParam)->startOfDay();
            $pendingRequest = AttendanceRequest::whereNull('attendance_id')
                ->where('user_id', $request->query('user_id'))
                ->whereDate('work_date', $date)
                ->where('status', 'pending')
                ->with('breakTimeRequests')
                ->latest()
                ->first();

            $displayAttendance = null;
            $displayBreaks = $pendingRequest?->breakTimeRequests ?? collect();
        } else {
            $attendance = Attendance::with('user')->findOrFail($id);
            $user = $attendance->user;
            $date = $attendance->work_date;
            $pendingRequest = $attendance->attendanceRequests()
                ->where('status', 'pending')
                ->with('breakTimeRequests')
                ->latest()
                ->first();
            if ($pendingRequest) {
                $displayAttendance = $pendingRequest;
                $displayBreaks = $pendingRequest->breakTimeRequests;
            } else {
                $displayAttendance = $attendance;
                $displayBreaks = $attendance->breakTimes;
            }
            // $displayAttendance = $pendingRequest ? $pendingRequest : $attendance;

            // $displayBreaks = $pendingRequest ? $pendingRequest->breakTimeRequests : $attendance->breakTimes;
        }
            return view('admin.attendance.detail', compact('user', 'attendance', 'pendingRequest', 'displayAttendance', 'displayBreaks', 'date'));
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = null;
        DB::transaction(function () use ($request, $id, &$attendance) {
            if ((int)$id === 0) {
                $date = Carbon::parse($request->query('date'))->startOfDay();

                $attendance = Attendance::create([
                    'user_id'   => $request->input('user_id'),
                    'work_date' => $date,
                    'clock_in'  => Carbon::parse($date->toDateString() . ' ' . $request->clock_in),
                    'clock_out' => Carbon::parse($date->toDateString() . ' ' . $request->clock_out),
                ]);
            } else {
                $attendance = Attendance::findOrFail($id);

                // pending があれば修正不可
                if ($attendance->attendanceRequests()->where('status', 'pending')->exists()) {
                    throw new \Exception('承認待ちの修正申請があります');
                }
            }

            $date = $attendance->work_date->toDateString();
            // ① 勤怠を更新
            $attendance->update([
                'clock_in'  => Carbon::parse("$date {$request->clock_in}"),
                'clock_out' => Carbon::parse("$date {$request->clock_out}"),
            ]);

            // ② 修正申請を作成
            $attendanceRequest = AttendanceRequest::create([
                'attendance_id'       => $attendance->id,
                'user_id'             => $attendance->user_id,
                'work_date'           => $attendance->work_date,
                'requested_clock_in'  => $attendance->clock_in,
                'requested_clock_out' => $attendance->clock_out,
                'reason'              => $request->reason,
                'status'              => 'approved',
                'approved_by'         => auth('admin')->id(),
            ]);

            // ③ 休憩の更新
            foreach ($request->breaks ?? [] as $break) {
                if (isset($break['id'])) {
                    $breakModel = $attendance->breakTimes()->find($break['id']);

                    if (empty($break['start']) && empty($break['end'])) {
                        $breakModel?->delete();
                        continue;
                    }

                    $breakModel?->update([
                        'break_start' => Carbon::parse("$date {$break['start']}"),
                        'break_end'   => Carbon::parse("$date {$break['end']}"),
                    ]);

                    BreakTimeRequest::create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'break_time_id'         => $break['id'],
                        'requested_break_start' => Carbon::parse("$date {$break['start']}"),
                        'requested_break_end'   => Carbon::parse("$date {$break['end']}"),
                    ]);

                    continue;
                }

                if (!empty($break['start']) && !empty($break['end'])) {
                    $newBreak = $attendance->breakTimes()->create([
                        'break_start' => Carbon::parse("$date {$break['start']}"),
                        'break_end'   => Carbon::parse("$date {$break['end']}"),
                    ]);

                    BreakTimeRequest::create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'break_time_id'         => $newBreak->id,
                        'requested_break_start' => $newBreak->break_start,
                        'requested_break_end'   => $newBreak->break_end,
                    ]);
                }
            }
        });

        return redirect()
            ->route('admin.attendance.detail', [
                'id'      => $attendance->id,
                'date'    => $attendance->work_date->toDateString(),
                'user_id' => $attendance->user_id,
            ])
            ->with('success', '勤怠を更新しました');
    }

    public function staffList (Request $request, User $user) {
        if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'));
            $year = $date->year;
            $month = $date->month;
        } else {
            $year = $request->input('year', now()->year);
            $month = $request->input('month', now()->month);
            $date = Carbon::create($year, $month, 1);
        }
        $attendances = Attendance::forMonth($user->id, $year, $month);
        $start = Attendance::monthDate($year, $month);
        $end   = $start->copy()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);
        $prevMonth = Attendance::prevMonth($year, $month);
        $nextMonth = Attendance::nextMonth($year, $month);

        return view('admin.staff.attendance', compact(
            'user',
            'attendances',
            'period',
            'year',
            'month',
            'date',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function downloadCsv (Request $request, User $user) {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $attendances = Attendance::forMonth($user->id, $year, $month);

        $start = Attendance::monthDate($year, $month);
        $end   = $start->copy()->endOfMonth();
        $period = CarbonPeriod::create($start, $end);

        $filename = "{$user->name}_{$year}_{$month}.csv";
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        $callback = function () use ($period, $attendances) {
            $output = fopen('php://output', 'w');
            stream_filter_prepend($output, 'convert.iconv.utf-8/cp932');
            fputcsv($output, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);
            foreach ($period as $date) {
                $key = $date->format('Y-m-d');
                $attendance = $attendances[$key] ?? null;

                fputcsv($output, [
                    $date->locale('ja')->isoFormat('MM/DD(ddd)'),
                    $attendance?->clock_in ? $attendance->clock_in->format('H:i') : '',
                    $attendance?->clock_out ? $attendance->clock_out->format('H:i') : '',
                    $attendance?->total_break_time ?? '',
                    $attendance?->total_work_time ?? '',
                ]);
            }
            fclose($output);
        };
        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
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

    public function detail (Attendance $attendance) {
        if (!auth('admin')->check()) {
            abort(403);
        }
        $pendingRequest = $attendance->latestPendingRequest();
        if ($pendingRequest) {
            return redirect()->route('admin.attendance_request.show', [
                'attendanceRequest' => $pendingRequest->id
            ]);
        }
        $breaks = $attendance->breakTimes()->orderBy('break_start')->get();
        return view('admin.attendance.detail', compact('attendance', 'breaks'));
    }

    public function update (UpdateAttendanceRequest $request, Attendance $attendance) {
        DB::transaction(function () use ($request, $attendance) {
            // 勤怠の更新
            $attendance->update([
                'clock_in'  => $request->clock_in,
                'clock_out' => $request->clock_out,
            ]);
            foreach ($request->breaks ?? [] as $break) {
                if (isset($break['id'])) {
                    $breakModel = $attendance->breakTimes()->find($break['id']);
                    if (empty($break['start']) && empty($break['end'])) {
                        if ($breakModel) {
                            $breakModel->delete();
                        }
                        continue;
                    }
                    if ($breakModel) {
                        $breakModel->update([
                            'break_start' => $break['start'],
                            'break_end'   => $break['end'],
                        ]);
                    }
                    continue;
                }
                if (!empty($break['start']) || !empty($break['end'])) {
                    $attendance->breakTimes()->create([
                        'break_start' => $break['start'],
                        'break_end'   => $break['end'],
                    ]);
                }
            }
        });
        return redirect()
            ->route('admin.attendance.detail', $attendance->id)
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

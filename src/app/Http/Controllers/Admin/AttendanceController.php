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
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $date = Carbon::create($year, $month, 1);
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

    public function downloadCsv(Request $request, User $user)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $attendances = Attendance::forMonth($user->id, $year, $month);

        $response = new StreamedResponse(function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            stream_filter_prepend($handle, 'convert.iconv.utf-8/cp932');
            // CSV のヘッダー
            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            // データ行
            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->work_date->format('Y-m-d'),
                    optional($attendance->clock_in)->format('H:i'),
                    optional($attendance->clock_out)->format('H:i'),
                    $attendance->total_break_time,
                    $attendance->total_work_time,
                ]);
            }

            fclose($handle);
        });

        // ダウンロードファイル名
        $fileName = "{$user->name}_{$year}_{$month}_attendance.csv";

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename=\"$fileName\"");

        return $response;
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Support\Facades\DB;

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
}

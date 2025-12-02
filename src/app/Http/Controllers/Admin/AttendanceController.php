<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
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
        $breaks = $attendance->breakTimes()->orderBy('break_start')->get();
        return view('admin.attendance.detail', compact('attendance', 'breaks'));
    }

    public function update (UpdateAttendanceRequest $request, Attendance $attendance) {
        if (!$attendance->isChangedFromOriginal($request)) {
            return redirect()
                ->route('admin.attendance.detail', $attendance->id)
                ->with('success', '変更がありませんでした');
        }
        // 変更があるときだけ Request を作る
        $attendance->createRequestFromUpdate($request);
        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('success', '勤怠を更新しました');
    }
}

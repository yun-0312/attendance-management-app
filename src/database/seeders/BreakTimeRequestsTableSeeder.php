<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRequest;
use App\Models\BreakTimeRequest;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimeRequestsTableSeeder extends Seeder
{
    public function run()
    {
        $attendanceRequests = AttendanceRequest::with('attendance.breakTimes')
            ->where('status', 'pending')    
            ->get();

        foreach ($attendanceRequests as $ar) {

            // 遅刻だけの理由なら休憩申請は作らない
            if ($ar->reason === "電車遅延のため") {
                continue;
            }

            // 30% の確率で休憩を修正
            if (rand(1, 100) <= 30) {

                foreach ($ar->attendance->breakTimes as $break) {

                    $date = $ar->attendance->work_date;

                    $newStart = Carbon::parse($break->break_start)->addMinutes(30);
                    $newEnd   = Carbon::parse($break->break_end)->addMinutes(30);

                    BreakTimeRequest::create([
                        'attendance_request_id' => $ar->id,
                        'break_time_id'         => $break->id,
                        'requested_break_start' => $newStart,
                        'requested_break_end'   => $newEnd,
                    ]);
                }
            }

            // 追加休憩（5%）
            if (rand(1, 100) <= 5) {

                $clockIn  = Carbon::parse($ar->requested_clock_in);
                $clockOut = Carbon::parse($ar->requested_clock_out);

                // ランダムな位置に60分休憩追加
                $start = $clockIn->copy()->addMinutes(rand(60, 180));
                $end   = $start->copy()->addMinutes(60);

                // 勤務時間外になる場合は作らない
                if ($end->lt($clockOut)) {
                    BreakTimeRequest::create([
                        'attendance_request_id' => $ar->id,
                        'break_time_id'         => null,
                        'requested_break_start' => $start,
                        'requested_break_end'   => $end,
                    ]);
                }
            }
        }
    }
}

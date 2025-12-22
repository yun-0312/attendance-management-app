<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRequest;
use App\Models\BreakTimeRequest;
use Carbon\Carbon;

class BreakTimeRequestsTableSeeder extends Seeder
{
    public function run()
    {
        $attendanceRequests = AttendanceRequest::with('attendance.breakTimes')
            ->where('status', 'pending')
            ->get();

        foreach ($attendanceRequests as $attendanceRequest) {

            $attendance = $attendanceRequest->attendance;
            if (!$attendance) {
                continue;
            }
            $breaks = $attendance->breakTimes;
            // 30% の確率で休憩を修正
            $shouldShift = rand(1, 100) <= 30;
            foreach ($breaks as $break) {
                if ($shouldShift) {
                    $newStart = Carbon::parse($break->break_start)->addMinutes(30);
                    $newEnd = Carbon::parse($break->break_end)->addMinutes(30);
                } else {
                    $newStart = $break->break_start;
                    $newEnd = $break->break_end;
                }
                BreakTimeRequest::create([
                    'attendance_request_id' => $attendanceRequest->id,
                    'requested_break_start' => $newStart,
                    'requested_break_end'   => $newEnd,
                ]);

            }
            // 追加休憩（5%）
            if (rand(1, 100) <= 5) {

                $clockIn  = Carbon::parse($attendanceRequest->requested_clock_in);
                $clockOut = Carbon::parse($attendanceRequest->requested_clock_out);

                // ランダムな位置に60分休憩追加
                $addStart = $clockIn->copy()->addMinutes(rand(60, 180));
                $addEnd   = $addStart->copy()->addMinutes(60);

                // 勤務時間外になる場合は作らない
                if ($addEnd->lt($clockOut)) {
                    BreakTimeRequest::create([
                        'attendance_request_id' => $attendanceRequest->id,
                        'requested_break_start' => $addStart,
                        'requested_break_end'   => $addEnd,
                    ]);
                }
            }
        }
    }
}
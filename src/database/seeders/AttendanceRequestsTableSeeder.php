<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\User;
use App\Models\BreakTimeRequest;
use Carbon\Carbon;

class AttendanceRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::where('role', 'admin')->first();

        $attendances = Attendance::with('breakTimes')->get();

        foreach ($attendances as $attendance) {

            // 30% の確率で pending の遅刻 or 退勤修正を作成
            if (rand(1, 100) <= 30) {
                $this->createPendingRequest($attendance);
            }

            // 10% の確率で approved のリクエストを作成
            if (rand(1, 100) <= 10) {
                $this->createApprovedRequest($attendance, $admin->id);
            }
        }
    }

    private function createPendingRequest($attendance)
    {
        $date = $attendance->work_date->toDateString();

        $isLate = rand(1, 100) <= 30; // 遅刻 30%

        if ($isLate) {
            $clockIn = Carbon::parse("$date 09:30");
            $clockOut = $attendance->clock_out;
            $reason = "電車遅延のため";
        } else {
            $clockIn = $attendance->clock_in;
            $clockOut = Carbon::parse("$date 18:30");
            $reason = "打刻誤りのため";
        }

        AttendanceRequest::create([
            'attendance_id'      => $attendance->id,
            'user_id'            => $attendance->user_id,
            'requested_clock_in' => $clockIn,
            'requested_clock_out' => $clockOut,
            'reason'             => $reason,
            'status'             => 'pending',
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);
    }

    private function createApprovedRequest($attendance, $adminId)
    {
        $request = AttendanceRequest::create([
            'attendance_id'      => $attendance->id,
            'user_id'            => $attendance->user_id,
            'requested_clock_in' => $attendance->clock_in,
            'requested_clock_out' => $attendance->clock_out,
            'reason'             => '打刻誤りのため',
            'status'             => 'approved',
            'approved_by'        => $adminId,
            'created_at'         => now(),
            'updated_at'         => now(),
        ]);

        foreach ($attendance->breakTimes as $break) {
            BreakTimeRequest::create([
                'attendance_request_id' => $request->id,
                'break_time_id'         => $break->id,
                'requested_break_start' => $break->break_start,
                'requested_break_end'   => $break->break_end,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }
}
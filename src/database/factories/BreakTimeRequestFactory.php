<?php

namespace Database\Factories;

use App\Models\BreakTimeRequest;
use App\Models\AttendanceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakTimeRequestFactory extends Factory
{
    protected $model = BreakTimeRequest::class;

    public function definition()
    {
        $attendanceRequest = AttendanceRequest::factory()->create();

        $clockIn  = Carbon::parse($attendanceRequest->requested_clock_in);
        $clockOut = Carbon::parse($attendanceRequest->requested_clock_out);

        // 勤務時間内でランダムに休憩（30〜60分）
        $breakStart = $clockIn->copy()->addMinutes(rand(60, 180));
        $breakEnd   = $breakStart->copy()->addMinutes(rand(30, 60));

        // 念のため勤務時間外を防ぐ
        if ($breakEnd->gte($clockOut)) {
            $breakEnd = $clockOut->copy()->subMinutes(10);
        }

        return [
            'attendance_request_id' => $attendanceRequest->id,
            'requested_break_start' => $breakStart,
            'requested_break_end'   => $breakEnd,
        ];
    }
}
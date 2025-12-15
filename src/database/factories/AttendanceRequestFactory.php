<?php

namespace Database\Factories;

use App\Models\AttendanceRequest;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;

    public function definition()
    {
        $attendance = Attendance::factory()->create();

        return [
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'work_date' => $attendance->work_date,
            'requested_clock_in' => Carbon::parse($attendance->clock_in)->addMinutes(rand(-30, 30)),
            'requested_clock_out'=> Carbon::parse($attendance->clock_out)->addMinutes(rand(-30, 30)),
            'reason' => '勤怠修正',
            'status' => 'pending',
            'approved_by' => null,
        ];
    }
}
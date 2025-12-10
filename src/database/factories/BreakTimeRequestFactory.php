<?php

namespace Database\Factories;

use App\Models\BreakTimeRequest;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakTimeRequestFactory extends Factory
{
    protected $model = BreakTimeRequest::class;

    public function definition()
    {
        $break = BreakTime::factory()->create();

        return [
            'attendance_request_id' => AttendanceRequest::factory(),
            'break_time_id' => $break->id,
            'requested_break_start' => Carbon::parse($break->break_start)->addMinutes(rand(-10, 10)),
            'requested_break_end'   => Carbon::parse($break->break_end)->addMinutes(rand(-10, 10)),
        ];
    }
}
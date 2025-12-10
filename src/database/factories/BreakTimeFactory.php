<?php

namespace Database\Factories;

use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        $attendance = Attendance::factory()->create();

        // 勤怠日の 12:00～13:00 を休憩としてデフォルト生成
        $date = Carbon::parse($attendance->work_date);

        $breakStart = $date->copy()->setTime(12, 0);
        $breakEnd   = $date->copy()->setTime(13, 0);

        return [
            'attendance_id' => $attendance->id,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ];
    }
}
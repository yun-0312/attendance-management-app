<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $clockIn = Carbon::now()->setTime(9, 0);
        $clockOut = (clone $clockIn)->addHours(9);

        return [
            'user_id' => User::factory(),
            'work_date' => $clockIn->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 'normal',
        ];
    }
}

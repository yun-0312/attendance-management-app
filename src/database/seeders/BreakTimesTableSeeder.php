<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class BreakTimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();
        foreach ($attendances as $attendance) {
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);
            // 勤務時間が短すぎる場合（休憩不可）
            if ($clockOut->diffInHours($clockIn) < 4) {
                continue;
            }
            // ⭐ 休憩回数を決定（5% の確率で 2～3 回）
            $multipleBreaks = rand(1, 100) <= 5;
            if ($multipleBreaks) {
                $breakCount = rand(2, 3);
            } else {
                $breakCount = 1;
            }
            // 休憩作成ループ
            for ($i = 0; $i < $breakCount; $i++) {
                // ⭐ 休憩開始時刻の範囲を決める
                if ($i === 0) {
                    // 1回目は昼休憩 → 12:00〜14:00
                    $breakStart = $clockIn->copy()->setTime(12, 0)->addMinutes(rand(0, 120));
                } else {
                    // 2回目以降は clock_in〜clock_out 内のランダム
                    $breakStart = $clockIn->copy()->addMinutes(rand(120, $clockOut->diffInMinutes($clockIn) - 60));
                }
                // ⭐ 休憩時間を決定（30〜60分）
                $breakDuration = rand(30, 60); // minutes
                $breakEnd = $breakStart->copy()->addMinutes($breakDuration);
                // ⭐ 退勤時間より後なら調整
                if ($breakEnd > $clockOut) {
                    $breakEnd = $clockOut->copy()->subMinutes(60);
                }
                // ⭐ 休憩開始が出勤より前にならないように
                if ($breakStart < $clockIn) {
                    $breakStart = $clockIn->copy()->addMinutes(60);
                }
                // ⭐ 不正な休憩データにならないよう保証
                if ($breakEnd <= $breakStart) {
                    continue;
                }
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $breakStart,
                    'break_end'     => $breakEnd,
                ]);
            }
        }
    }
}

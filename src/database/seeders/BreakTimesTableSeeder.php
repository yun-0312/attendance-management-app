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
            $date = Carbon::parse($attendance->work_date);
            $clockIn = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            // =========================
            // ① 基本休憩（12:00〜13:00）
            // =========================
            $basicStart = $date->copy()->setTime(12, 0);
            $basicEnd   = $date->copy()->setTime(13, 0);

            $breaks = [
                [
                    'break_start' => $basicStart,
                    'break_end'   => $basicEnd,
                ]
            ];

            // =========================
            // ② 3% の確率で追加休憩（2〜3 回）
            // =========================
            if (rand(1, 100) <= 3) {
                $extraCount = rand(2, 3);

                for ($i = 0; $i < $extraCount; $i++) {

                    $duration = [30, 60][array_rand([30, 60])];

                    // ----- 開始時間候補 -----
                    $minStart = $clockIn->copy()->addMinutes(30);
                    $maxStart = $clockOut->copy()->subMinutes($duration + 10);

                    if ($minStart >= $maxStart) {
                        continue;
                    }
                    $start = Carbon::createFromTimestamp(
                        rand($minStart->timestamp, $maxStart->timestamp)
                    );
                    $end = $start->copy()->addMinutes($duration);

                    // =========================
                    // 重複チェック
                    // =========================
                    $overlap = false;
                    foreach ($breaks as $b) {
                        if ($start->lt($b['break_end']) && $b['break_start']->lt($end)) {
                            $overlap = true;
                            break;
                        }
                    }
                    if ($overlap) continue;

                    $breaks[] = [
                        'break_start' => $start,
                        'break_end'   => $end,
                    ];
                }
            }

            // =========================
            //  ③ break_times テーブルに挿入
            // =========================
            foreach ($breaks as $bt) {
                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $bt['break_start'],
                    'break_end'     => $bt['break_end'],
                ]);
            }
        }
    }
}
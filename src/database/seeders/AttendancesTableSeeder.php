<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        $days = 180;

        foreach ($users as $user) {

            for ($i = 0; $i < $days; $i++) {

                // i 日前の日付
                $date = Carbon::today()->subDays($i);

                // 土日は勤務なし → attendance を作らない
                if ($date->isWeekend()) {
                    continue;
                }

                // 出勤時間は毎日固定 9:00
                $clockIn = $date->copy()->setTime(9, 0, 0);

                // 退勤時間は 17:00〜20:00 の中でランダム
                $hour = rand(17, 20);
                $minute = rand(0, 59);

                $clockOut = $date->copy()->setTime($hour, $minute);

                // 必ず clock_out > clock_in
                if ($clockOut <= $clockIn) {
                    $clockOut = $clockIn->copy()->addHours(1);
                }

                // ステータスはランダム
                $statusList = ['normal', 'pending', 'approved'];
                $status = $statusList[array_rand($statusList)];

                DB::table('attendances')->insert([
                    'user_id'   => $user->id,
                    'work_date' => $date->toDateString(),
                    'clock_in'  => $clockIn,
                    'clock_out' => $clockOut,
                    'status'    => $status,
                    'created_at'=> now(),
                    'updated_at'=> now(),
                ]);
            }
        }
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function freezeNow()
    {
        Carbon::setTestNow(now());  // 現在時刻を固定
    }

    protected function createAdmin()
    {
        return User::factory()->create([
            'role' => 'admin'
        ]);
    }

    protected function createUserAttendance($user, $date, $clockIn = '09:00', $clockOut = '18:00')
    {
        return Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in' => $date . " $clockIn",
            'clock_out' => $date . " $clockOut",
        ]);
    }

    protected function createBreak($attendance, $start = '12:00', $end = '13:00')
    {
        $date = $attendance->work_date->toDateString();
        return BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse("$date $start"),
            'break_end' => Carbon::parse("$date $end"),
        ]);
    }

    /** @test */
    // その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function admin_can_see_all_users_attendance_for_today()
    {
        $this->freezeNow();   // now() を固定（重要）

        $admin = $this->createAdmin();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // 今日の勤怠データ（2 名分）
        $attendance1 = $this->createUserAttendance($user1, today()->toDateString(), '09:00', '18:00');
        $attendance2 = $this->createUserAttendance($user2, today()->toDateString(), '10:00', '19:00');

        // 休憩データ追加
        $this->createBreak($attendance1, '12:00', '13:00');
        $this->createBreak($attendance2, '13:00', '14:00');

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        // 全ユーザー分が表示されているか
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        //勤怠が表示されているか
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        //休憩合計時間が表示されているか
        $response->assertSee('1:00');
    }

    /** @test */
    // 遷移した際に現在の日付が表示される
    public function today_date_is_displayed_on_admin_attendance_list()
    {
        $this->freezeNow();

        $admin = $this->createAdmin();
        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list');

        $response->assertSee(today()->format('Y/m/d')); // 画面に日付が表示される
    }

    /** @test */
    // 「前日」を押下した時に前の日の勤怠情報が表示される
    public function admin_can_view_previous_day_attendance()
    {
        $this->freezeNow();

        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $yesterday = today()->subDay()->toDateString();

        // 昨日の勤怠を作成
        $this->createUserAttendance($user, $yesterday, '08:00', '17:00');

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list?date=' . $yesterday);

        $response->assertSee('08:00');
        $response->assertSee('17:00');

        $response->assertSee(Carbon::parse($yesterday)->format('Y/m/d'));
    }

    /** @test */
    // 「翌日」を押下した時に次の日の勤怠情報が表示される
    public function admin_can_view_next_day_attendance()
    {
        $this->freezeNow();

        $admin = $this->createAdmin();
        $user = User::factory()->create();

        $tomorrow = today()->addDay()->toDateString();

        // 明日の勤怠を作成
        $this->createUserAttendance($user, $tomorrow, '11:00', '20:00');

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/attendance/list?date=' . $tomorrow);

        $response->assertSee('11:00');
        $response->assertSee('20:00');

        $response->assertSee(Carbon::parse($tomorrow)->format('Y/m/d'));
    }
}
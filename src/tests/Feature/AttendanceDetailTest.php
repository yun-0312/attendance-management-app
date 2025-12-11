<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    // テスト用データ作成
    private function createUserAndAttendance()
    {
        // ユーザー作成
        $user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        // 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse('2025-01-15'),
            'clock_in' => Carbon::parse('2025-01-15 09:00'),
            'clock_out' => Carbon::parse('2025-01-15 18:00'),
        ]);

        // 休憩時間データ
        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => Carbon::parse('2025-01-15 12:00'),
            'break_end' => Carbon::parse('2025-01-15 13:00'),
        ]);

        return [$user, $attendance];
    }

    /** @test */
    // 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function user_name_is_displayed_on_attendance_detail_page()
    {
        [$user, $attendance] = $this->createUserAndAttendance();

        // ログイン
        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    /** @test */
    // 勤怠詳細画面の「日付」が選択した日付になっている
    public function selected_date_is_displayed_on_attendance_detail_page()
    {
        [$user, $attendance] = $this->createUserAndAttendance();

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('2025年');
        $response->assertSee('1月15日');
    }

    /** @test */
    // 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function clock_in_and_clock_out_times_match_the_attendance_record()
    {
        [$user, $attendance] = $this->createUserAndAttendance();

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    // 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function break_times_are_displayed_correctly_on_attendance_detail_page()
    {
        [$user, $attendance] = $this->createUserAndAttendance();

        $this->actingAs($user);

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
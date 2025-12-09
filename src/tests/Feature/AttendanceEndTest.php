<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 退勤ボタンが正しく機能する
    public function end_button_is_displayed_when_user_is_working()
    {
        // 今日の出勤データを作成（勤務中）
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'clock_in'  => now()->subHours(2),
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance');

        // 「退勤」ボタンの表示を確認
        $response->assertSee('退勤');

        // 退勤処理を実行
        $response = $this->actingAs($user)
            ->post('/attendance/end');
        $response->assertRedirect('/attendance');

        // DB の退勤時刻が更新されていることを確認
        $attendance->refresh();
        $this->assertNotNull($attendance->clock_out);

        // ステータス確認
        $screen = $this->actingAs($user)
            ->get('/attendance');
        $screen->assertSee('退勤済');
    }

    /** @test */
    // 退勤時刻が勤怠一覧画面で確認できる
    public function end_time_is_displayed_correctly_in_list_screen()
    {
        $user = User::factory()->create();

        // 出勤 → 退勤のデータ
        $clockIn  = Carbon::today()->setTime(9, 0);
        $clockOut = Carbon::today()->setTime(18, 0);

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $formatTime = $clockOut->format('H:i');
        $response->assertSee($formatTime);
    }
}

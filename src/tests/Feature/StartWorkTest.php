<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartWorkTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 出勤ボタンが正しく機能する
    public function user_can_start_work_and_status_becomes_working()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        // 「出勤」ボタンが表示されている
        $response->assertSee('出勤');

        // 出勤処理
        $response = $this->post('/attendance/start');

        // リダイレクト成功
        $response->assertRedirect('/attendance');

        // DB に保存されている
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => today(),
        ]);

        // 画面に「勤務中」が表示される
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    // 出勤は一日一回のみできる
    public function start_button_is_not_visible_after_user_has_already_checked_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => today(),
            'clock_in'  => now(),
            'clock_out' => now(),
        ]);

        // 退勤済み → 出勤ボタン非表示
        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    /** @test */
    // 出勤時刻が勤怠一覧画面で確認できる
    public function start_time_is_displayed_in_attendance_list_after_check_in()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤処理
        $this->post('/attendance/start');

        // 勤怠一覧へ
        $response = $this->get('/attendance/list');

        // 出勤時刻（H:i の部分だけ照合）
        $time = now()->format('H:i');

        $response->assertSee($time);
    }
}
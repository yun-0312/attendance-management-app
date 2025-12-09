<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 休憩ボタンが正しく機能する
    public function break_start_button_is_displayed_and_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // まず出勤させる
        $this->post('/attendance/start');

        // 出勤中画面に休憩入ボタンが出ること
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        // 休憩入
        $this->post('/attendance/break-start');

        // ステータスが休憩中になる
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /** @test */
    // 休憩は一日に何回でもできる
    public function user_can_take_break_multiple_times_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/start');

        // 1回目 休憩入 → 戻る
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        // 2回目の休憩入ボタンが表示されること
        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    /** @test */
    // 休憩戻ボタンが正しく機能する
    public function break_end_button_is_displayed_and_works()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/start');

        // 休憩入 → 休憩中になる
        $this->post('/attendance/break-start');
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        // 休憩戻 → 出勤中に戻る
        $this->post('/attendance/break-end');
        $response = $this->get('/attendance');
        $response->assertSee('勤務中');
    }

    /** @test */
    // 休憩戻は一日に何回でもできる
    public function user_can_end_break_multiple_times_a_day()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/start');

        // 1回目の休憩入 → 戻る
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        // 2回目の休憩入
        $this->post('/attendance/break-start');

        // 休憩戻ボタンが表示される
        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');
    }

    /** @test */
    // 休憩時刻が勤怠一覧画面で確認できる
    public function break_times_are_displayed_in_list_screen()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/start');

        // 休憩入 & 戻る
        $this->post('/attendance/break-start');
        $this->post('/attendance/break-end');

        $attendance = Attendance::first();

        // 勤怠一覧画面アクセス（当月）
        $response = $this->get('/attendance/list?year=' . now()->year . '&month=' . now()->month);

        // 休憩時刻が表示されていること
        $response->assertSee($attendance->breakTimes[0]->break_start->format('H:i'));
        $response->assertSee($attendance->breakTimes[0]->break_end->format('H:i'));
    }
}

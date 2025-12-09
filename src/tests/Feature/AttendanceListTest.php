<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 自分が行った勤怠情報が全て表示されている
    public function user_can_see_all_their_attendance_records()
    {
        $user = User::factory()->create();

        // 3日分の勤怠データを作成
        $attendances = Attendance::factory()
            ->count(3)
            ->for($user)
            ->sequence(
                ['work_date' => '2025-12-01'],
                ['work_date' => '2025-12-02'],
                ['work_date' => '2025-12-03'],
            )
            ->create();

        $this->actingAs($user);
        $response = $this->get('/attendance/list?date' . '2025-02-01');
        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->work_date->locale('ja')->isoFormat('MM/DD(ddd)'));
        }
    }

    /** @test */
    // 勤怠一覧画面に遷移した際に現在の月が表示される
    public function attendance_list_shows_current_month_by_default()
    {
        $user = User::factory()->create();
        $today = Carbon::today();
        $monthString = $today->format('Y/m');

        $this->actingAs($user);
        $response = $this->get('/attendance/list');
        $response->assertSee($monthString);
    }

    /** @test */
    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function user_can_see_previous_month_attendance()
    {
        $user = User::factory()->create();

        // 前月の日付
        $previousMonth = Carbon::now()->subMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $previousMonth->copy()->day(10),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?year=' . $previousMonth->year . '&month=' . $previousMonth->month);

        $response->assertSee($previousMonth->format('Y/m'));
        $response->assertSee($attendance->work_date->locale('ja')->isoFormat('MM/DD(ddd)'));
    }

    /** @test */
    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function user_can_see_next_month_attendance()
    {
        $user = User::factory()->create();

        $nextMonth = Carbon::now()->addMonth();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $nextMonth->copy()->day(15),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list?year=' . $nextMonth->year . '&month=' . $nextMonth->month);

        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee($attendance->work_date->locale('ja')->isoFormat('MM/DD(ddd)'));
    }

    /** @test */
    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function clicking_detail_button_redirects_to_correct_attendance_detail_page()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-12-10',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        // 詳細ボタンのリンクが存在するかチェック
        $response->assertSee('/attendance/detail/' . $attendance->id);

        // 実際にアクセスできるか確認
        $detailResponse = $this->get('/attendance/detail/' . $attendance->id);

        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($attendance->work_date->format('Y年'));
    }
}

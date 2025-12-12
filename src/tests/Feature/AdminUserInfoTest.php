<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;

class AdminUserInfoTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    // 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function admin_can_view_all_users_basic_info()
    {
        $admin = $this->createAdmin();

        // 一般ユーザーを複数作成
        $users = User::factory()->count(3)->create(['role' => 'user']);

        $this->actingAs($admin, 'admin');

        $response = $this->get('/admin/staff/list');

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    /** @test */
    // ユーザーの勤怠情報が正しく表示される
    public function admin_can_view_selected_users_attendance()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        // 勤怠データ3日分作成
        $attendances = Attendance::factory()
            ->count(3)
            ->for($user)
            ->sequence(
                [
                    'work_date' => '2025-12-01',
                    'clock_in'  => '09:00',
                    'clock_out' => '18:00',
                ],
                [
                    'work_date' => '2025-12-02',
                    'clock_in'  => '09:15',
                    'clock_out' => '18:30',
                ],
                [
                    'work_date' => '2025-12-03',
                    'clock_in'  => '10:00',
                    'clock_out' => '19:00',
                ],
            )
            ->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get("/admin/attendance/staff/{$user->id}");

        foreach ($attendances as $attendance) {
            $clockIn  = $attendance->clock_in?->format('H:i');
            $clockOut = $attendance->clock_out?->format('H:i');

            $response->assertSee($clockIn);
            $response->assertSee($clockOut);
        }
    }

    /** @test */
    // 「前月」を押下した時に表示月の前月の情報が表示される
    public function admin_can_view_previous_month_attendance()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        Attendance::factory()
            ->count(2)
            ->for($user)
            ->sequence(
                [
                    'work_date' => '2025-11-01',
                    'clock_in'  => '09:00',
                    'clock_out' => '18:00',
                ],
                [
                    'work_date' => '2025-12-01',
                    'clock_in'  => '09:15',
                    'clock_out' => '18:30',
                ],
            )
            ->create();

        $this->actingAs($admin, 'admin');

        // 前月パラメータでリクエスト
        $response = $this->get("/admin/attendance/staff/{$user->id}?year=2025&month=11");

        // 前月の年月と勤怠が表示されているか
        $response->assertSee('2025/11');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    // 「翌月」を押下した時に表示月の前月の情報が表示される
    public function admin_can_view_next_month_attendance()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        Attendance::factory()
            ->count(2)
            ->for($user)
            ->sequence(
                [
                    'work_date' => '2025-12-01',
                    'clock_in'  => '09:00',
                    'clock_out' => '18:00',
                ],
                [
                    'work_date' => '2026-01-01',
                    'clock_in'  => '09:15',
                    'clock_out' => '18:30',
                ],
            )
            ->create();

        $this->actingAs($admin, 'admin');

        // 翌月パラメータでリクエスト
        $response = $this->get("/admin/attendance/staff/{$user->id}?year=2026&month=01");

        // 翌月の年月と勤怠が表示されているか
        $response->assertSee('2026/01');
        $response->assertSee('09:15');
        $response->assertSee('18:30');
    }

    /** @test */
    // 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function admin_can_navigate_to_attendance_detail_from_list()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-02-10',
        ]);

        $this->actingAs($admin, 'admin');

        // 詳細ページへアクセス
        $response = $this->get(route('admin.attendance.detail', $attendance->id));

        // 正常にページが表示されること
        $response->assertStatus(200);

        // 勤怠日付が表示されていること
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));
    }
}
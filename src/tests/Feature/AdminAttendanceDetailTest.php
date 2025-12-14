<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createAttendanceWithBreak($user, $date = null)
    {
        $date = $date ?: today()->toDateString();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $date,
            'clock_in' => "$date 09:00",
            'clock_out' => "$date 18:00",
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => "$date 12:00",
            'break_end'   => "$date 13:00",
        ]);

        return $attendance;
    }

    /** @test */
    // 勤怠詳細画面に表示されるデータが選択したものになっている
    public function admin_can_view_correct_attendance_detail()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();

        $attendance = $this->createAttendanceWithBreak($user);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.attendance.detail', $attendance->id));

        $response->assertStatus(200);

        // 名前
        $response->assertSee($user->name);

        // 日付表示（Y年 n月j日）
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月j日'));

        // 出勤・退勤
        $response->assertSee('09:00');
        $response->assertSee('18:00');

        // 休憩
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /** @test */
    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function admin_cannot_set_clock_in_after_clock_out()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();
        $attendance = $this->createAttendanceWithBreak($user);

        $this->actingAs($admin, 'admin');

        $response = $this->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ],
                'reason' => 'test'
            ]
        );

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function admin_cannot_set_break_start_after_clock_out()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();
        $attendance = $this->createAttendanceWithBreak($user);

        $this->actingAs($admin, 'admin');

        $response = $this->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '19:00', 'end' => '20:00']
                ],
                'reason' => 'test'
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function admin_cannot_set_break_end_after_clock_out()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();
        $attendance = $this->createAttendanceWithBreak($user);

        $this->actingAs($admin, 'admin');

        $response = $this->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '19:00']
                ],
                'reason' => 'test'
            ]
        );

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function admin_must_input_reason()
    {
        $admin = $this->createAdmin();
        $user  = User::factory()->create();
        $attendance = $this->createAttendanceWithBreak($user);

        $this->actingAs($admin, 'admin');

        $response = $this->post(
            route('admin.attendance.update', $attendance->id),
            [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    ['start' => '12:00', 'end' => '13:00']
                ],
                'reason' => ''   // 未入力
            ]
        );

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }
}
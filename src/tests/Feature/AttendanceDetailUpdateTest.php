<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    // テスト用データ作成
    private function createUserWithAttendance()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password123'),
            'role' => 'user',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        // 休憩時間のFactoryを使用
        $break1 = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        return [$user, $attendance, collect([$break1])];
    }

    /** @test */
    // 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_cannot_be_after_clock_out()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $this->actingAs($user);

        $response = $this->patch(route('attendance.update', $attendance->id), [
            'clock_in' => '20:00',
            'clock_out' => '10:00',
            'reason' => 'test reason',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00']
            ]
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /** @test */
    // 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_cannot_be_after_clock_out()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $this->actingAs($user);

        $response = $this->patch(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'test reason',
            'breaks' => [
                ['start' => '20:00', 'end' => '21:00']
            ],
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /** @test */
    // 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_cannot_be_after_clock_out()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $this->actingAs($user);

        $response = $this->patch(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => 'test reason',
            'breaks' => [
                ['start' => '12:00', 'end' => '20:00']
            ]
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /** @test */
    // 備考欄が未入力の場合のエラーメッセージが表示される
    public function test_reason_is_required()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $this->actingAs($user);

        $response = $this->patch(route('attendance.update', $attendance->id), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'reason' => '',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00']
            ]
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください'
        ]);
    }
}
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceRequest;

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

    /** @test */
    // 修正申請処理が実行される
    public function test_update_creates_attendance_request()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $this->actingAs($user);

        $this->patch(route('attendance.update', $attendance->id), [
            'clock_in' => '08:30',
            'clock_out' => '18:00',
            'reason' => '早く出勤しました',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00']
            ]
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    // 	「承認待ち」にログインユーザーが行った申請が全て表示されていること
    public function test_pending_requests_are_listed_for_user()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance_request.list'));
        $response->assertSee($request->reason);
    }

    /** @test */
    //　「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_approved_requests_are_listed_for_user()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'approved'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance_request.list'));
        $response->assertSee($request->reason);
    }

    /** @test */
    // 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
    public function test_request_detail_link_goes_to_attendance_detail()
    {
        [$user, $attendance] = $this->createUserWithAttendance();

        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance_request.detail', $request->id));
        $response->assertStatus(200);
        $response->assertSee($attendance->work_date->format('Y年'));
    }
}
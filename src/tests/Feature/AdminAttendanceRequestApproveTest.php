<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceRequestApproveTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /** @test */
    // 承認待ちの修正申請が全て表示されている
    public function pending_requests_are_listed_for_admin()
    {
        $admin = $this->createAdmin();

        // 修正申請5件（全て pending）
        $requests = AttendanceRequest::factory()
            ->count(5)
            ->state(['status' => 'pending'])
            ->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/list?tab=pending');

        foreach ($requests as $req) {
            $response->assertSee($req->reason);
        }
    }

    /** @test */
    // 承認済みの修正申請が全て表示されている
    public function approved_requests_are_listed_for_admin()
    {
        $admin = $this->createAdmin();

        $requests = AttendanceRequest::factory()
            ->count(5)
            ->state(['status' => 'approved'])
            ->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get('/stamp_correction_request/list?tab=approved');

        foreach ($requests as $req) {
            $response->assertSee($req->reason);
        }
    }

    /** @test */
    // 修正申請の詳細内容が正しく表示されている
    public function admin_can_view_request_detail()
    {
        $admin = $this->createAdmin();

        $attendance = Attendance::factory()->create();
        $request = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get("/stamp_correction_request/approve/{$request->id}");

        $response->assertSee($request->reason);
        $response->assertSee($request->requested_clock_in->format('H:i'));
        $response->assertSee($request->requested_clock_out->format('H:i'));
    }

    /** @test */
    // 修正申請の承認処理が正しく行われる
    public function admin_can_approve_request_and_update_attendance()
    {
        $admin = $this->createAdmin();

        $attendance = Attendance::factory()->create([
            'clock_in'  => '09:00',
            'clock_out' => '18:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'attendance_id'       => $attendance->id,
            'status'              => 'pending',
            'requested_clock_in'  => '10:00',
            'requested_clock_out' => '19:00',
            'reason'              => '修正理由',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->patch("/stamp_correction_request/approve/{$request->id}");

        $response->assertRedirect();

        // 勤怠情報が更新されていること
        $this->assertDatabaseHas('attendances', [
            'id'       => $attendance->id,
            'clock_in' => $attendance->work_date->format('Y-m-d') . ' 10:00:00',
            'clock_out' => $attendance->work_date->format('Y-m-d') . ' 19:00:00',
        ]);

        // 承認済みになっていること
        $this->assertDatabaseHas('attendance_requests', [
            'id'     => $request->id,
            'status' => 'approved',
        ]);
    }
}
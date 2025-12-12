<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 勤務外の場合、勤怠ステータスが正しく表示される
    public function it_shows_status_as_off_duty_when_no_attendance_exists()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /** @test */
    // 出勤中の場合、勤怠ステータスが正しく表示される
    public function it_shows_status_as_working_when_clocked_in()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務中');
    }

    /** @test */
    // 休憩中の場合、勤怠ステータスが正しく表示される
    public function it_shows_status_as_breaking_when_user_is_in_break()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(2),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subHour(),
            'break_end' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /** @test */
    // 退勤済の場合、勤怠ステータスが正しく表示される
    public function it_shows_status_as_finished_when_clocked_out()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
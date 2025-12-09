<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CurrentDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 現在の日時情報がUIと同じ形式で出力されている
    public function attendance_screen_displays_current_datetime()
    {
        // テスト用ユーザー作成 & ログイン
        $user = User::factory()->create();
        $this->actingAs($user);

        // 表示されるべき現在日時（UI と同じフォーマット）
        $now = now()->locale('ja');
        $formattedDate = $now->isoFormat('YYYY年M月D日(ddd)');
        $formattedTime = $now->format('H:i');

        // 画面取得
        $response = $this->get('/attendance');

        // 日付が見えるか
        $response->assertSee($formattedDate);

        // 時刻が見えるか
        $response->assertSee($formattedTime);
    }
}

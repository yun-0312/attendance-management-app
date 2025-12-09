<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    // 名前が未入力の場合、バリデーションメッセージが表示される
    public function name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @test */
    // メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    /** @test */
    // パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function password_must_be_at_least_8_characters()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    /** @test */
    // パスワードが一致しない場合、バリデーションメッセージが表示される
    public function password_confirmation_must_match()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different456',
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
        ]);
    }

    /** @test */
    // パスワードが未入力の場合、バリデーションメッセージが表示される
    public function password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    // フォームに内容が入力されていた場合、データが正常に保存される
    public function user_can_register_successfully()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // DB にユーザーが保存されているか確認
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 登録後のリダイレクト（Fortify は verify.notice に飛ばす）
        $response->assertRedirect('/email/verify');
    }
}

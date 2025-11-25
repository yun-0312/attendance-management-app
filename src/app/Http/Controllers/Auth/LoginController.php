<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(LoginRequest $request) {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('ログイン情報が登録されていません'),
            ]);
        }

        $request->session()->regenerate();
        $user = Auth::user();
        if (!$user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')
                ->with('message', 'メールアドレスの確認が必要です。登録時に送信されたメールを確認してください。');
        }
        return redirect()->intended(route('attendance.index'));
    }
}
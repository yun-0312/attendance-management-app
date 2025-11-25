<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ログイン
Route::post('/login', [LoginController::class, 'store'])->name('login');

//メール認証画面
Route::get('/email/verify', function () {
    return view('user.auth.verify-email');
})->middleware('auth')->name('verification.notice');

//メール認証リンククリック時
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    $user = $request->user();
    if (!$user->profile) {
        return redirect()->route('attendance.index')
            ->with('success', 'メール認証が完了しました。');
    }
    return redirect()->route('attendance.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

//認証メール再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '確認メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::middleware(['auth', 'verified'])->group(function () {
    // 勤怠トップページ
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 出勤
    Route::post('/attendance/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    // 退勤
    Route::post('/attendance/end', [AttendanceController::class, 'end'])
        ->name('attendance.end');
    
    // 休憩入り
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break.start');

    // 休憩終わり
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break.end');

});


<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\AttendanceRequestController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;
use App\Http\Controllers\Unified\AttendanceRequestController as UnifiedAttendanceRequestController;

// ログイン
Route::post('/login', [LoginController::class, 'store'])->name('login');

// ログアウト
Route::post('/logout', function () {
    auth()->logout();
    return redirect()->route('login');
})->name('logout');

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

Route::prefix('attendance')->middleware(['auth', 'verified'])->group(function () {
    // 勤怠トップページ
    Route::get('/', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 出勤
    Route::post('/start', [AttendanceController::class, 'start'])
        ->name('attendance.start');

    // 退勤
    Route::post('/end', [AttendanceController::class, 'end'])
        ->name('attendance.end');

    // 休憩開始
    Route::post('/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break.start');

    // 休憩終了
    Route::post('/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break.end');

    // 勤怠一覧画面（一般ユーザー）
    Route::get('/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 勤怠詳細画面（一般ユーザー）
    Route::get('/detail/{attendance}', [AttendanceController::class, 'detail'])
        ->name('attendance.detail')
        ->middleware('auth');

    // 勤怠修正（一般ユーザー）
    Route::post('/detail/{attendance}', [AttendanceController::class, 'update'])
        ->name('attendance.update')
        ->middleware('auth');
});

// 申請一覧（共通 URL）
Route::get(
    '/stamp_correction_request/list',
    [UnifiedAttendanceRequestController::class, 'index']
)->middleware('multi_auth')->name('attendance_request.list');


Route::middleware(['auth', 'verified'])->group(function () {
    // 申請詳細画面（一般ユーザー）
    Route::get('/stamp_correction_request/detail/{attendanceRequest}', [AttendanceRequestController::class, 'detail'])->name('attendance_request.detail');
});

// 管理者ログインページ
Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');

// 管理者ログイン処理
Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');

// 管理者ログアウト
Route::post('/admin/logout', function () {
    auth('admin')->logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect()->route('login');
})->name('admin.logout');

// 管理者専用
Route::prefix('admin')->middleware(['is_admin'])->group(function () {

    // 勤怠一覧（管理者）
    Route::get('/attendance/list', [AdminAttendanceController::class, 'list'])
            ->name('admin.attendance.list');

    // 勤怠詳細画面（管理者）
    Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'detail'])
        ->name('admin.attendance.detail')
        ->middleware('auth:admin');

    // 勤怠詳細修正処理（管理者）
    Route::patch('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update')
        ->middleware('auth:admin');

    // スタッフ一覧画面（管理者）
    Route::get('/staff/list', [StaffController::class, 'index'])
        ->name('admin.staff.index');

    // スタッフ別勤怠一覧画面（管理者）
    Route::get('/attendance/staff/{user}', [AdminAttendanceController::class, 'staffList'])
        ->name('admin.staff.attendance');

    // スタッフ別勤怠一覧ｃｓｖ出力
    Route::get(
        '/admin/attendance/staff/{user}/csv',
        [AdminAttendanceController::class, 'downloadCsv']
    )
        ->name('admin.staff.attendance.csv');
});

Route::middleware(['is_admin'])->group(function () {
    // 申請詳細承認用（管理者）
    Route::get('/stamp_correction_request/approve/{attendanceRequest}', [AdminAttendanceRequestController::class, 'show'])
        ->name('admin.attendance_request.show');

    // 申請承認処理（管理者）
    Route::patch('/stamp_correction_request/approve/{attendanceRequest}', [AdminAttendanceRequestController::class, 'approve'])
        ->name('admin.attendance_request.approve');
});


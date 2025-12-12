<?php

namespace App\Http\Controllers\Unified;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\User\AttendanceRequestController as UserController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminController;

class AttendanceRequestController extends Controller
{
    public function index()
    {
        if (Auth::guard('admin')->check()) {
            return app(AdminController::class)->list();
        }

        // web ログイン（一般ユーザー）
        return app(UserController::class)->list();
    }
}
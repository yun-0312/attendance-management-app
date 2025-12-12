<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class MultiAuth
{
    public function handle($request, Closure $next)
    {
        // 管理者ログイン中なら admin ガードのみ有効
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');
            return $next($request);
        }

        // 一般ユーザーなら web ガードのみ有効
        if (Auth::guard('web')->check()) {
            Auth::shouldUse('web');
            return $next($request);
        }

        // どちらでもなければログイン画面へ
        return redirect()->route('login');
    }
}
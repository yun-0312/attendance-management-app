<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/layouts/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/layouts/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header-logo">
            <a class="header-logo__link" href="{{ route('attendance.index') }}">
                <img src="{{ asset('img/logo.svg') }}" alt="logo" class="header-logo__image">
            </a>
        </div>
        @if (Auth::guard('admin')->check())
        <ul class="admin__header-nav">
            <li class="header-nav__item">
                <a href="{{ route('admin.attendance.list') }}" class="header-nav__link">勤怠一覧</a>
            </li>
            <li class="header-nav__item">
                <a href="{{ route('admin.staff.index') }}" class="header-nav__link">スタッフ一覧</a>
            </li>
            <li class="header-nav__item">
                <a href="{{ route('attendance_request.list') }}" class="header-nav__link">申請一覧</a>
            </li>
            <li class="header-nav__item">
                <form action="{{ route('admin.logout') }}" class="header-nav__logout" method="post">
                    @csrf
                    <button class="header-nav__link">ログアウト</button>
                </form>
            </li>
        </ul>
        @elseif (Auth::guard('web')->check())
        <ul class="header-nav">
            <li class="header-nav__item">
                <a href="{{ route('attendance.index') }}" class="header-nav__link">勤怠</a>
            </li>
            <li class="header-nav__item">
                <a href="{{ route('attendance.list') }}" class="header-nav__link">勤怠一覧</a>
            </li>
            <li class="header-nav__item">
                <a href="{{ route('attendance_request.list') }}" class="header-nav__link">申請</a>
            </li>
            <li class="header-nav__item">
                <form action="{{ route('logout') }}" class="header-nav__logout" method="post">
                    @csrf
                    <button class="header-nav__link">ログアウト</button>
                </form>
            </li>
        </ul>
        @endif
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>
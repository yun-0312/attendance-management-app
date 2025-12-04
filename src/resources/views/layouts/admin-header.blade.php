<header class="header">
    <div class="header-logo">
        <a class="header-logo__link" href="{{ route('admin.attendance.list') }}">
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
            <a href="{{ route('attendance.request.list') }}" class="header-nav__link">申請一覧</a>
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
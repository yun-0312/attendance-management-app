<header class="header">
    <div class="header-logo">
        <a class="header-logo__link" href="/">
            <img src="{{ asset('img/logo.svg') }}" alt="logo" class="header-logo__image">
        </a>
    </div>
    @if (Auth::check())
        <ul class="header-nav">
            <li class="header-nav__item">
                <a href="{{ route('attendance.index') }}" class="header-nav__link">勤怠</a>
            </li>
            <li class="header-nav__item">
                <a href="{{ route('attendance.index') }}" class="header-nav__link">勤怠一覧</a>
            </li>
            <li class="header-nav__item">
                <a href="{{ route('attendance.index') }}" class="header-nav__link">申請</a>
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
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/register.css') }}" />
@endsection

@section('content')
<div class="content-wrapper">
    <h1 class="register-form__heading">会員登録</h1>
    <form class="form" action="{{ route('register') }}" method="post">
        @csrf
        <div class="register-form__group">
            <label class="register-form__label" for="name">ユーザー名</label>
            <input type="text" class="register-form__input" name="name" value="{{ old('name') }}">
            <p class="register-form__error-message">
                @error('name')
                {{ $message }}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <label class="register-form__label" for="email">メールアドレス</label>
            <input type="text" class="register-form__input" name="email" value="{{ old('email') }}">
            <p class="register-form__error-message">
                @error('email')
                {{ $message }}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <label class="register-form__label" for="password">パスワード</label>
            <input type="password" class="register-form__input" name="password">
            <p class=" register-form__error-message">
                @error('password')
                {{ $message }}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <label class="register-form__label" for="password_confirmation">パスワード確認</label>
            <input type="password" class="register-form__input" name="password_confirmation">
            <p class="register-form__error-message">
                @error('password_confirmation')
                {{ $message }}
                @enderror
            </p>
        </div>
        <div class="register-form__group">
            <input type="submit" class="register-form__btn" value="登録する">
        </div>
    </form>
    <div class="login__link">
        <a class="login__link-link" href="{{ route('login') }}">ログインはこちら</a>
    </div>
</div>

@endsection
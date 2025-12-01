@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/auth/login.css') }}" />
@endsection

@section('header')
@include('layouts.admin-header')
@endsection

@section('content')
<div class="content-wrapper">
    <h2 class="login-form__heading">管理者ログイン</h2>
    <form class="form" action="{{ route('login') }}" method="post">
        @csrf
        <input type="hidden" name="is_admin_login" value="1">
        <div class="login-form__group">
            <label class="login-form__label" for="email">メールアドレス</label>
            <input type="text" id="email" class="login-form__input" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="login-form__error-message">
                {{ $message }}
            </p>
            @enderror
        </div>
        <div class="login-form__group">
            <label class="login-form__label" for="password">パスワード</label>
            <input type="password" id="password" class="login-form__input" name="password">
            @error('password')
            <p class="login-form__error-message">
                {{ $message }}
            </p>
            @enderror
        </div>
        <div class="login-form__group">
            <input type="submit" class="login-form__btn" value="管理者ログインする">
        </div>
    </form>
</div>

@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/auth/verify-email.css') }}" />
@endsection

@section('content')
<div class="container">
    <p class="verify__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>
    <a href="http://localhost:8025" class="verify__button" target="blank" target="_blank" rel="noopener noreferrer">認証はこちらから</a>
    </a>
    <form class="resend__form" method="post" action="{{ route('verification.send') }}">
        @csrf
        <input type="submit" class="verify__resend" value="認証メールを再送する">
    </form>

</div>
@endsection
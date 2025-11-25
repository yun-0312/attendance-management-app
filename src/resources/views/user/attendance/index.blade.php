@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/index.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')
@if (session('succsess'))
<p class="success-message">
    {{ session('success') }}
</p>
@endif
<div class="attendance-container">
    <div class="attendance-status">
    @if (!$attendance)
        <span class="attendance-status__label">勤務外</span>
    @else
        <span class="attendance-status__label">{{ $attendance->current_status }}</span>
    @endif
    </div>

    <div class="attendance-date">
        {{ now()->locale('ja')->isoFormat('YYYY年M月D日(ddd)') }}
    </div>

    <div class="attendance-time">
        {{ now()->format('H:i') }}
    </div>

    <div class="attendance-actions">

        {{-- 勤務外 --}}
        @if (!$attendance || $attendance->current_status === '勤務外')
            <form action="{{ route('attendance.start') }}" method="post">
                @csrf
                <button class="attendance-btn">出勤</button>
            </form>

        {{-- 勤務中 --}}
        @elseif ($attendance->current_status === '勤務中')
            <form action="{{ route('attendance.end') }}" method="post" style="display:inline-block;">
                @csrf
                <button class="attendance-btn">退勤</button>
            </form>

            <form action="{{ route('attendance.break.start') }}" method="post" style="display:inline-block;">
                @csrf
                <button class="attendance-btn">休憩入</button>
            </form>

        {{-- 休憩中 --}}
        @elseif ($attendance->current_status === '休憩中')
            <form action="{{ route('attendance.break.end') }}" method="post">
                @csrf
                <button class="attendance-btn">休憩戻る</button>
            </form>

        {{-- 退勤済 --}}
        @elseif ($attendance->current_status === '退勤済')
            <p class="attendance-status-message">お疲れさまでした。</p>
        @endif
    </div>
</div>
@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')
<div class="detail-container">
    <h2 class="detail-title">勤怠詳細</h2>
    <form class="detail__form" action="{{ route('attendance.update', ['attendance' => $attendance->id])}}" method="post">
        @csrf
        @method('PATCH')
<table class="detail-table">
            <tr>
                <th>名前</th>
                <td>{{ auth()->user()->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    {{ $attendance->work_date->format('Y年') }}
                    {{ $attendance->work_date->format('n月j日') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in"
                        value="{{ $attendance->clock_in->format('H:i') }}"> ～
                    <input type="time" name="clock_out"
                        value="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}">
                </td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>
                    @if(isset($breaks[0]))
                    <input type="time" name="breaks[0][start]" value="{{ isset($breaks[0]) ? $breaks[0]->break_start->format('H:i') : '' }}"> ～
                    <input type="time" name="breaks[0][end]" value="{{ isset($breaks[0]) && $breaks[0]->break_end ? $breaks[0]->break_end->format('H:i') : '' }}">
                    @endif
                </td>
            </tr>
            <tr>
                <th>休憩2</th>
                <td>
                    @if(isset($breaks[1]))
                    {{ $breaks[1]->break_start->format('H:i') }} ～
                    {{ $breaks[1]->break_end ? $breaks[1]->break_end->format('H:i') : '-' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td><textarea name="reason" rows="4" class="detail-textarea"
                        placeholder="例：打刻漏れのため">{{ old('reason') }}</textarea></td>
            </tr>
        </table>
        <div class="detail-footer">
            <a href="" class="detail-edit-btn">修正</a>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection


@section('header')
@include('layouts/user-header')
@endsection


@section('content')
<h2 class="detail-title">修正申請の詳細</h2>
<table class="detail-table">
    <tr>
        <th>名前</th>
        <td>{{ $attendanceRequest->user->name }}</td>
    </tr>

    <tr>
        <th>対象日</th>
        <td>{{ $attendance->work_date->format('Y年n月j日') }}</td>
    </tr>

    <tr>
        <th>出勤・退勤</th>
        <td>
            {{ $attendanceRequest->requested_clock_in->format('H:i') }}
            〜
            {{ $attendanceRequest->requested_clock_out->format('H:i') }}
        </td>
    </tr>

    @foreach($breaks as $i => $break)
    <tr>
        <th>休憩{{ $i + 1 }}</th>
        <td>
            {{ optional($break->requested_break_start)->format('H:i') }}
            〜
            {{ optional($break->requested_break_end)->format('H:i') }}
        </td>
    </tr>
    @endforeach

    <tr>
        <th>申請理由</th>
        <td>{{ $attendanceRequest->reason }}</td>
    </tr>

    <tr>
        <th>状態</th>
        <td>{{ $attendanceRequest->status }}</td>
    </tr>
</table>

@endsection
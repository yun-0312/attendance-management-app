@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')

<h2 class="attendance-list__title">勤怠一覧</h2>

<div class="month-pagination">
    @foreach($months as $index => $m)
        <a href="{{ route('attendance.list', ['page' => $index+1]) }}"
            class="{{ $currentMonth->year == $m->year && $currentMonth->month == $m->month ? 'active' : '' }}">
            {{ $m->year }}年{{ $m->month }}月
        </a>
    @endforeach
</div>



<table class="attendance__table">
    <thead>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendances as $attendance)
        <tr>
            <td>{{ $attendance->work_date->locale('ja')->isoFormat('M/D(ddd)') }}</td>
            <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
            <td>{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '-' }}</td>
            <td>{{ $attendance?->total_break_time ?? '-' }}</td>
            <td>{{ $attendance?->total_work_time ?? '-' }}</td>
            <td>
                <a href="">修正申請</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
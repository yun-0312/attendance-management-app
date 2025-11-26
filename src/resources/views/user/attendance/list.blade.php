@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')
<div class="attendance-list__container">
    <h2 class="attendance-list__title">勤怠一覧</h2>
    <div class="month-nav">
        <a href="{{ route('attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
            class="month-nav__link"><span class="month-nav__link-item">←</span> 前月</a>

        <span class="month-nav__current">{{ $year }}/{{ $month }}</span>

        <a href="{{ route('attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
            class="month-nav__link">翌月 <span class="month-nav__link-item">→</span></a>
    </div>

    <table class="attendance__table">
        <thead class="attendance__table-thead">
            <tr>
                <th class="attendance__table-header">日付</th>
                <th class="attendance__table-header">出勤</th>
                <th class="attendance__table-header">退勤</th>
                <th class="attendance__table-header">休憩</th>
                <th class="attendance__table-header">合計</th>
                <th class="attendance__table-header">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($period as $date)
            @php
            $key = $date->format('Y-m-d');
            $attendance = $attendances[$key] ?? null;
            @endphp
            <tr>
                <td class="attendance__table-data">{{ $date->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                <td class="attendance__table-data">{{ $attendance?->clock_in ? $attendance->clock_in->format('H:i') : '' }}</td>
                <td class="attendance__table-data">{{ $attendance?->clock_out ? $attendance->clock_out->format('H:i') : '' }}</td>
                <td class="attendance__table-data">{{ $attendance?->total_break_time ?? '' }}</td>
                <td class="attendance__table-data">{{ $attendance?->total_work_time ?? '' }}</td>
                <td class="attendance__table-data">
                    <a href="" class="attendance__table-link">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
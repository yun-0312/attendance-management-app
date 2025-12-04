@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/attendance.css') }}">
@endsection

@section('header')
@include('layouts/admin-header')
@endsection

@section('content')
<div class="attendance-list__container">
    <h2 class="attendance-list__title">{{ $user->name }}さんの勤怠</h2>
    <div class="month-nav">
        <a href="{{ route('admin.staff.attendance', ['user' => $user->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
            class="month-nav__link"><span class="month-nav__link-item">←</span> 前月</a>
        <div class="date-selector">
            <form method="get" action="{{ route('admin.staff.attendance', $user->id) }}" id="dateForm" class="date-selector__form">
                <label class="calendar-icon">
                    <input
                        type="date"
                        name="date"
                        class="date-selector__input"
                        value="{{ $date->format('Y-m-d') }}"
                        onchange="document.getElementById('dateForm').submit();">
                    <span class="icon">
                        <img src="{{ asset('img/calender.png') }}" alt="calender">
                    </span>
                </label>
                <span class="selected-date">
                    {{ $date->format('Y/m') }}
                </span>
            </form>
        </div>
        <a href="{{ route('admin.staff.attendance', ['user' => $user->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
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
                    @if ($attendance)
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="attendance__table-link">詳細</a>
                    @else
                    <a href="" class="attendance__table-link">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <a class="download__btn"
        href="{{ route('admin.staff.attendance.csv', ['user' => $user->id, 'year' => $year, 'month' => $month]) }}">
        CSV出力
    </a>
</div>
@endsection
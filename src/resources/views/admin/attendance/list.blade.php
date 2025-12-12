@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('content')
<div class="list-container">
    <h2 class="list-title">{{ $date->format('Y年n月j日') }}の勤怠</h2>

    <div class="date-nav">
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}"
            class="date-nav__link"><span class="date-nav__link-item">←</span> 前日</a>
        <div class="date-selector">
            <form method="get" action="{{ route('admin.attendance.list') }}" id="dateForm" class="date-selector__form">
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
                    {{ $date->format('Y/m/d') }}
                </span>
            </form>
        </div>
        <a href="{{ route('admin.attendance.list', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}"
            class="date-nav__link">翌日 <span class="date-nav__link-item">→</span></a>
    </div>

    <table class="list-table">
        <thead>
            <tr class="list-table__column">
                <th class="list-table__header column-name">名前</th>
                <th class="list-table__header column-time">出勤</th>
                <th class="list-table__header column-time">退勤</th>
                <th class="list-table__header column-time">休憩</th>
                <th class="list-table__header column-time">合計</th>
                <th class="list-table__header column-detail">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr class="list-table__column">
                <td class="list-table__data column-name">{{ $attendance->user->name }}</td>
                <td class="list-table__data column-time">{{ optional($attendance->clock_in)->format('H:i') }}</td>
                <td class="list-table__data column-time">{{ optional($attendance->clock_out)->format('H:i') }}</td>
                <td class="list-table__data column-time">{{ $attendance->total_break_time }}</td>
                <td class="list-table__data column-time">{{ $attendance->total_work_time }}</td>
                <td class="list-table__data column-detail">
                    <a class="list-table__link" href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
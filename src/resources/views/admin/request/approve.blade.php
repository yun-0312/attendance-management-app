@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request/approve.css') }}">
@endsection


@section('header')
@include('layouts/admin-header')
@endsection


@section('content')
<div class="approve-container">
    <h2 class="approve-title">勤怠詳細</h2>
    <table class="approve-table">
        <tr class="approve-table__column">
            <th class="approve-table__header">名前</th>
            <td class="approve-table__data">{{ $attendanceRequest->user->name }}</td>
        </tr>
        <tr class="approve-table__column">
            <th class="approve-table__header">日付</th>
            <td class="approve-table__data">
                <span class="approve-table__data-item">{{ $attendance->work_date->format('Y年') }}</span>
                <span class="approve-table__data-item">{{ $attendance->work_date->format('n月j日') }}</span>
            </td>
        </tr>
        <tr class="approve-table__column">
            <th class="approve-table__header">出勤・退勤</th>
            <td class="approve-table__data">
                <div class="time-row">
                    <p class="time__item">{{ $attendanceRequest->requested_clock_in->format('H:i') }}</p>
                    <span class="time-separator">～</span>
                    <p class="time__item">{{ $attendanceRequest->requested_clock_out->format('H:i') }}</p>
                </div>
            </td>
        </tr>
        @foreach($breaks as $i => $break)
        <tr class="approve-table__column">
            <th class="approve-table__header">休憩{{ $i + 1 }}</th>
            <td class="approve-table__data">
                <div class="time-row">
                    <p class="time__item">{{ optional($break->requested_break_start)->format('H:i') }}</p>
                    <span class="time-separator">～</span>
                    <p class="time__item">{{ optional($break->requested_break_end)->format('H:i') }}</p>
                </div>
            </td>
        </tr>
        @endforeach
        <tr class="approve-table__column">
            <th class="approve-table__header">申請理由</th>
            <td class="approve-table__data">{{ $attendanceRequest->reason }}</td>
        </tr>
    </table>
    @if ($attendanceRequest->status === 'pending')
    <div class="approve-footer">
        <form action="{{ route('attendance.request.approve', $attendanceRequest->id) }}" method="post" class="approve__form">
        @csrf
        @method('PATCH')
        <button type="submit" class="approve__btn">承認</button>
    </div>
    @else
    <div class="approve-footer">
        <button type="button" class="approved__btn">承認済み</button>
    </div>
    @endif
</div>
@endsection
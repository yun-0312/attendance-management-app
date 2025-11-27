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
            <tr class="detail-table__column">
                <th class="detail-table__header">名前</th>
                <td class="detail-table__data">{{ auth()->user()->name }}</td>
            </tr>
            <tr class="detail-table__column">
                <th class="detail-table__header">日付</th>
                <td class="detail-table__data">
                    <span class="detail-table__data-item">{{ $attendance->work_date->format('Y年') }}</span>
                    <span class="detail-table__data-item">{{ $attendance->work_date->format('n月j日') }}</span>
                </td>
            </tr>
            <tr class="detail-table__column">
                <th class="detail-table__header">出勤・退勤</th>
                <td class="detail-table__data">
                    <input type="time" name="clock_in"
                        value="{{ $attendance->clock_in->format('H:i') }}"> ～
                    <input type="time" name="clock_out"
                        value="{{ $attendance->clock_out ? $attendance->clock_out->format('H:i') : '' }}">
                </td>
            </tr>
            @foreach($breaks as $i => $break)
            <tr class="detail-table__column">
                <th class="detail-table__header">休憩{{ ++$i }}</th>
                <td class="detail-table__data">
                    <input type="time" name="breaks[{{ $i }}][start]"
                        value="{{ old("breaks.$i.start", $break->break_start->format('H:i')) }}">
                    ～
                    <input type="time" name="breaks[{{ $i }}][end]"
                        value="{{ old("breaks.$i.end", optional($break->break_end)->format('H:i')) }}">
                </td>
            </tr>
            @endforeach
            <tr class="detail-table__column">
                <th class="detail-table__header">休憩{{ count($breaks) + 1 }}</th>
                <td class="detail-table__data">
                    <input type="time" name="breaks[{{ count($breaks) }}][start]">
                    ～
                    <input type="time" name="breaks[{{ count($breaks) }}][end]">
                </td>
            </tr>
            <tr class="detail-table__column">
                <th class="detail-table__header">備考</th>
                <td class="detail-table__data">
                    <textarea name="reason" rows="4" class="detail-textarea">{{ old('reason') }}</textarea>
                </td>
            </tr>
        </table>
        <div class="detail-footer">
            <a href="" class="detail-edit-btn">修正</a>
        </div>
    </form>
</div>
@endsection
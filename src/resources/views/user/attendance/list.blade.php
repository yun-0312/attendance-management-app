@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/list.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')

<h2 class="attendance-list-title">勤怠一覧</h2>

<table class="attendance-table">
    <thead>
        <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>勤務時間</th>
            <th>申請</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendances as $attendance)
        <tr>
            <td>{{ $attendance->work_date->locale('ja')->isoFormat('M/D(ddd)') }}</td>
            <td>{{ $attendance->clock_in ? $attendance->clock_in->format('H:i') : '-' }}</td>
            <td>@if ($attendance->clock_in && $attendance->clock_out)
                {{ $attendance->clock_out->diffInMinutes($attendance->clock_in) }} 分
                @else
                -
                @endif
            </td>

            {{-- 休憩時間合計 --}}
            <td>
                {{ $attendance->breakTimes->sum(function($break){
                    if($break->break_end){
                        return $break->break_end->diffInMinutes($break->break_start);
                    }
                    return 0;
                }) }} 分
            </td>

            {{-- 勤務時間（退勤-出勤 - 休憩） --}}
            <td>
                @if ($attendance->clock_out)
                {{ $attendance->clock_out->diffInMinutes($attendance->clock_in)
                        - $attendance->breakTimes->sum(function($b){
                            return $b->break_end
                                ? $b->break_end->diffInMinutes($b->break_start)
                                : 0;
                        })
                    }} 分
                @else
                -
                @endif
            </td>

            <td>
                <a href="{{ route('attendance.request', $attendance->id) }}">修正申請</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{ $attendances->links() }}

@endsection
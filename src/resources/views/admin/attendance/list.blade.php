@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/list.css') }}">
@endsection

@section('header')
@include('layouts/admin-header')
@endsection

@section('content')
<h2>全ユーザー勤怠一覧（{{ $date }}）</h2>

<form method="get" action="{{ route('admin.attendance.list') }}">
    <input type="date" name="date" value="{{ $date }}">
    <button type="submit">表示</button>
</form>

<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attendances as $att)
        <tr>
            <td>{{ $att->user->name }}</td>
            <td>{{ optional($att->clock_in)->format('H:i') }}</td>
            <td>{{ optional($att->clock_out)->format('H:i') }}</td>
            <td>{{ $att->total_break_time }}</td>
            <td>{{ $att->total_work_time }}</td>
            <td>
                <a href="{{ route('attendance.detail', $att->id) }}">詳細</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection

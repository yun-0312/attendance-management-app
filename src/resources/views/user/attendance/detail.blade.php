@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/detail.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')
@if (session('success'))
<p class="success-message">
    {{ session('success') }}
</p>
@endif
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
                    <div class="time-row">
                        <input type="text" name="clock_in" class="time__input" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                        <span class="time-separator">～</span>
                        <input type="text"  name="clock_out" class="time__input" value="{{ old('clock_out', $attendance->clock_out ? $attendance->clock_out->format('H:i') : '') }}">
                    </div>
                    @error('clock_in')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                    @error('clock_out')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </td>
            </tr>
            @foreach($breaks as $i => $break)
            <tr class="detail-table__column">
                <th class="detail-table__header">休憩{{ ++$i }}</th>
                <td class="detail-table__data">
                    <div class="time-row">
                        <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id }}">
                        <input type="test" name="breaks[{{ $i }}][start]" class="time__input"
                            value="{{ old("breaks.$i.start", $break->break_start->format('H:i')) }}">
                        <span class="time-separator">～</span>
                        <input type="text" name="breaks[{{ $i }}][end]" class="time__input"
                            value="{{ old("breaks.$i.end", optional($break->break_end)->format('H:i')) }}">
                    </div>
                    @error('breaks.$i.start')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                    @error('breaks.$i.end')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </td>
            </tr>
            @endforeach
            <tr class="detail-table__column">
                <th class="detail-table__header">休憩{{ count($breaks) + 1 }}</th>
                <td class="detail-table__data">
                    <div class="time-row">
                        <input type="text" name="breaks[{{ count($breaks) }}][start]" class="time__input" value="{{ old("breaks.$i.start") }}">
                        <span class="time-separator">～</span>
                        <input type="text" name="breaks[{{ count($breaks) }}][end]" class="time__input" value="{{ old("breaks.$i.start") }}">
                    </div>
                    @error('breaks.*.start')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                    @error('breaks.*.end')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </td>
            </tr>
            <tr class="detail-table__column">
                <th class="detail-table__header">備考</th>
                <td class="detail-table__data">
                    <textarea name="reason" rows="4" class="detail-textarea">{{ old('reason') }}</textarea>
                    @error('reason')
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </td>
            </tr>
        </table>
        <div class="detail-footer">
            <button type="submit" class="detail-edit-btn">修正</button>
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
@if (session('success'))
<p class="success-message">
    {{ session('success') }}
</p>
@endif
@if (session('error'))
<p class="error-message">
    {{ session('error') }}
</p>
@endif
<div class="detail-container">
    <h1 class="detail-title">勤怠詳細</h1>
    @php
    $isNew = is_null($attendance);
    dd($pendingRequest);
    @endphp
    <form class="detail__form" action="{{ route('admin.attendance.update', [
        'id'      => $attendance?->id ?? 0,
        'user_id' => $user->id,
        'date'    => $date->toDateString(),
    ]) }}" method="post">
        @csrf
        <table class="detail-table">
            <tr class="detail-table__column">
                <th class="detail-table__header">名前</th>
                <td class="detail-table__data">{{ $user->name }}</td>
            </tr>
            <tr class="detail-table__column">
                <th class="detail-table__header">日付</th>
                <td class="detail-table__data">
                    <span class="detail-table__data-item">{{ $date->format('Y年') }}</span>
                    <span class="detail-table__data-item">{{ $date->format('n月j日') }}</span>
                </td>
            </tr>
            <tr class="detail-table__column">
                <th class="detail-table__header">出勤・退勤</th>
                <td class="detail-table__data">
                    <div class="time-row">
                        <input type="text" name="clock_in" class="time__input @if($pendingRequest) readonly-input @endif" value="{{ old('clock_in', optional($displayAttendance?->clock_in)->format('H:i')) }}" @if($pendingRequest) disabled @endif>
                        <span class="time-separator">～</span>
                        <input type="text" name="clock_out" class="time__input @if($pendingRequest) readonly-input @endif" value="{{ old('clock_out', $displayAttendance?->clock_out ? $displayAttendance->clock_out->format('H:i') : '') }}" @if($pendingRequest) disabled @endif>
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
            @foreach($displayBreaks as $i => $break)
            <tr class="detail-table__column">
                <th class="detail-table__header">休憩{{ $i + 1 }}</th>
                <td class="detail-table__data">
                    <div class="time-row">
                        <input type="hidden" name="breaks[{{ $i }}][id]" value="{{ $break->id }}">
                        @php
                        $start = $break->break_start ?? $break->requested_break_start;
                        $end = $break->break_end ?? $break->requested_break_end;
                        @endphp
                        <input type="text" name="breaks[{{ $i }}][start]" class="time__input @if($pendingRequest) readonly-input @endif"
                            value="{{ old("breaks.$i.start", optional($start)->format('H:i')) }}" @if($pendingRequest) disabled @endif>
                        <span class="time-separator">～</span>
                        <input type="text" name="breaks[{{ $i }}][end]" class="time__input @if($pendingRequest) readonly-input @endif"
                            value="{{ old("breaks.$i.end", optional($end)->format('H:i')) }}" @if($pendingRequest) disabled @endif>
                    </div>
                    @error("breaks.$i.start")
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                    @error("breaks.$i.end")
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </td>
            </tr>
            @endforeach

            @php
            $newIndex = count($displayBreaks);
            @endphp
            @if(!$pendingRequest)
            <tr class="detail-table__column">
                <th class="detail-table__header">休憩{{ $newIndex + 1 }}</th>
                <td class="detail-table__data">
                    <div class="time-row">
                        <input type="text" name="breaks[{{ $newIndex }}][start]" class="time__input" value="{{ old("breaks.$newIndex.start") }}">
                        <span class="time-separator">～</span>
                        <input type="text" name="breaks[{{ $newIndex }}][end]" class="time__input" value="{{ old("breaks.$newIndex.end") }}">
                    </div>
                    @error("breaks.$newIndex.start")
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                    @error("breaks.$newIndex.end")
                    <p class="detail-form__error-message">
                        {{ $message }}
                    </p>
                    @enderror
                </td>
            </tr>
            @endif
            <tr class="detail-table__column">
                <th class="detail-table__header">備考</th>
                <td class="detail-table__data">
                    <div class="textarea-wrapper @if($pendingRequest) readonly-mode @endif">
                        <textarea name="reason" rows="4" class="detail-textarea @if($pendingRequest) readonly-textarea @endif" @if($pendingRequest) disabled @endif>{{ $pendingRequest ? $pendingRequest->reason : old('reason') }}</textarea>
                        @error('reason')
                        <p class="detail-form__error-message">
                            {{ $message }}
                        </p>
                        @enderror
                    </div>
                </td>
            </tr>
        </table>
        <div class="detail-footer">
            @if($pendingRequest)
            <p class="pending-message">*承認待ちのため修正はできません。</p>
            @else
            <button type="submit" class="detail-edit-btn">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('header')
@include('layouts/admin-header')
@endsection

@section('content')
<div class="list-container">
    <h2 class="list-title">スタッフ一覧</h2>

    <table class="list-table">
        <thead>
            <tr class="list-table__column">
                <th class="list-table__header column-name">名前</th>
                <th class="list-table__header column-time">メールアドレス</th>
                <th class="list-table__header column-time">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr class="list-table__column">
                <td class="list-table__data column-name">{{ $user->name }}</td>
                <td class="list-table__data column-time">{{ $user->email }}</td>
                <td class="list-table__data column-detail">
                    <a class="list-table__link" href="{{ route('admin.staff.attendance', $user->id) }}">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/request/list.css') }}">
@endsection

@section('header')
@include('layouts/user-header')
@endsection

@section('content')
<div class="request-list__container">
    <h2 class="request-list__title">申請一覧</h2>

    <div class="tabs">
        <button class="tab-btn active" data-tab="pending">承認待ち</button>
        <button class="tab-btn" data-tab="approved">承認済み</button>
    </div>

    <div class="tab-content active" id="pending">
        <table class="request-table">
            <thead>
                <tr>
                    <th class="request-table__header">状態</th>
                    <th class="request-table__header">名前</th>
                    <th class="request-table__header">対象日時</th>
                    <th class="request-table__header">申請理由</th>
                    <th class="request-table__header">申請日時</th>
                    <th class="request-table__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pending as $req)
                <tr>
                    <td class="request-table_body">承認待ち</td>
                    <td class="request-table_body">{{ $req->user->name }}</td>
                    <td class="request-table_body">{{ $req->attendance->work_date->format('Y/m/d') }}</td>
                    <td class="request-table_body">{{ Str::limit($req->reason, 20) }}</td>
                    <td class="request-table_body">{{ $req->created_at->format('Y/m/d H:i') }}</td>
                    <td class="request-table_body">
                        <a href="{{ route('attendanceRequest.detail', $req->id) }}" class="request-table__link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="request-table_body" colspan="6">申請はありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="tab-content" id="approved">
        <table class="request-table">
            <thead>
                <tr>
                    <th class="request-table__header">状態</th>
                    <th class="request-table__header">名前</th>
                    <th class="request-table__header">対象日時</th>
                    <th class="request-table__header">申請理由</th>
                    <th class="request-table__header">申請日時</th>
                    <th class="request-table__header">詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approved as $req)
                <tr>
                    <td class="request-table_body">承認済み</td>
                    <td class="request-table_body">{{ $req->user->name }}</td>
                    <td class="request-table_body">{{ $req->attendance->work_date->format('Y/m/d') }}</td>
                    <td class="request-table_body">{{ Str::limit($req->reason, 20) }}</td>
                    <td class="request-table_body">{{ $req->created_at->format('Y/m/d H:i') }}</td>
                    <td class="request-table_body"><a href="{{ route('attendanceRequest.detail', $req->id) }}" class="request-table__link">詳細</a></td>
                </tr>
                @empty
                <tr>
                    <td class="request-table_body" colspan="6">承認済みの申請はありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {

            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            let tab = this.dataset.tab;
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.getElementById(tab).classList.add('active');
        });
    });
</script>
@endsection
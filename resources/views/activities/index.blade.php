@extends('layouts.app')
@section('title','Nhật ký hoạt động')
@section('content')
<div class="page-wrap">
    <div class="page-head"><div><h1>Nhật ký hoạt động</h1><p>Theo dõi đăng nhập và các thay đổi dữ liệu.</p></div></div>

    @if(auth()->user()->isAdmin())
        <form method="get" action="{{ route('activities.index') }}" class="panel activity-filter">
            <label for="employee-filter">Nhân viên</label>
            <input id="employee-filter" type="search" name="employee" value="{{ request('employee') }}" maxlength="255" placeholder="Nhập tên, username hoặc email nhân viên...">
            <button class="btn primary" type="submit">Tìm kiếm</button>
            @if(request('employee'))<a class="btn ghost" href="{{ route('activities.index') }}">Đặt lại</a>@endif
        </form>
    @endif

    <section class="panel table-wrap">
        <table class="data-table">
            <thead><tr><th>Thời gian</th><th>Người dùng</th><th>Hành động</th><th>Nội dung</th><th>IP</th></tr></thead>
            <tbody>
            @forelse($activities as $activity)
                <tr>
                    <td>{{ $activity->created_at?->format('d/m/Y H:i:s') }}</td>
                    <td><strong>{{ $activity->user?->name ?? 'Hệ thống' }}</strong><small>{{ $activity->user?->email }}</small></td>
                    <td><span class="action-chip">{{ $activity->action }}</span></td>
                    <td>{{ $activity->description }}</td>
                    <td>{{ $activity->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Không tìm thấy hoạt động phù hợp.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>
    @if($activities->hasPages())<div class="pagination">{{ $activities->links() }}</div>@endif
</div>
@endsection


@extends('layouts.app')
@section('title','Nhật ký hoạt động')
@section('content')
<div class="page-wrap"><div class="page-head"><div><h1>Nhật ký hoạt động</h1><p>Theo dõi đăng nhập và các thay đổi dữ liệu.</p></div></div><section class="panel table-wrap"><table class="data-table"><thead><tr><th>Thời gian</th><th>Người dùng</th><th>Hành động</th><th>Nội dung</th><th>IP</th></tr></thead><tbody>@forelse($activities as $activity)<tr><td>{{ $activity->created_at?->format('d/m/Y H:i:s') }}</td><td><strong>{{ $activity->user?->name ?? 'Hệ thống' }}</strong><small>{{ $activity->user?->email }}</small></td><td><span class="action-chip">{{ $activity->action }}</span></td><td>{{ $activity->description }}</td><td>{{ $activity->ip_address }}</td></tr>@empty<tr><td colspan="5">Chưa có hoạt động.</td></tr>@endforelse</tbody></table></section><div class="pagination">{{ $activities->links() }}</div></div>
@endsection


@extends('layouts.app')
@section('title','Người dùng')
@section('content')
<div class="page-wrap">
    <div class="page-head"><div><h1>Người dùng & phân quyền</h1><p>Cấp quyền theo dự án hoặc từng mã căn.</p></div><a class="btn primary" href="{{ route('admin.users.create') }}">+ Tạo tài khoản</a></div>
    <form method="get" action="{{ route('admin.users.index') }}" class="panel user-search-form">
        <input type="search" name="q" value="{{ request('q') }}" maxlength="255" placeholder="Tìm theo tên, username hoặc email...">
        <button class="btn primary" type="submit">Tìm kiếm</button>
        @if(request('q'))<a class="btn ghost" href="{{ route('admin.users.index') }}">Đặt lại</a>@endif
    </form>
    <section class="panel table-wrap"><table class="data-table"><thead><tr><th>Người dùng</th><th>Vai trò</th><th>Dự án</th><th>Căn riêng</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
    @forelse($users as $user)
        <tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td><td><span class="role-chip">{{ strtoupper($user->role) }}</span></td><td>{{ $user->projects_count }}</td><td>{{ $user->properties_count }}</td><td><span class="status-dot {{ $user->is_active ? 'on' : '' }}"></span>{{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</td><td><div class="user-row-actions"><a class="btn outline-green" href="{{ route('admin.users.edit',$user) }}">Sửa quyền</a>@unless($user->is(auth()->user()))<form method="post" action="{{ route('admin.users.destroy',$user) }}" onsubmit="return confirm('Xóa tài khoản {{ $user->email }}?')">@csrf @method('DELETE')<button class="btn danger" type="submit">Xóa</button></form>@endunless</div></td></tr>
    @empty
        <tr><td colspan="6">Không tìm thấy người dùng phù hợp.</td></tr>
    @endforelse
    </tbody></table></section>
@if($users->hasPages())<nav class="pagination">{{ $users->links() }}</nav>@endif</div>
@endsection


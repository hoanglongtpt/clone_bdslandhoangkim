@extends('layouts.app')
@section('title','Người dùng')
@section('content')
<div class="page-wrap"><div class="page-head"><div><h1>Người dùng & phân quyền</h1><p>Cấp quyền theo dự án hoặc từng mã căn.</p></div><a class="btn primary" href="{{ route('admin.users.create') }}">+ Tạo tài khoản</a></div>
<section class="panel table-wrap"><table class="data-table"><thead><tr><th>Người dùng</th><th>Vai trò</th><th>Dự án</th><th>Căn riêng</th><th>Trạng thái</th><th></th></tr></thead><tbody>@foreach($users as $user)<tr><td><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></td><td><span class="role-chip">{{ strtoupper($user->role) }}</span></td><td>{{ $user->projects_count }}</td><td>{{ $user->properties_count }}</td><td><span class="status-dot {{ $user->is_active ? 'on' : '' }}"></span>{{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</td><td><a class="btn outline-green" href="{{ route('admin.users.edit',$user) }}">Sửa quyền</a></td></tr>@endforeach</tbody></table></section>
@if($users->hasPages())<nav class="pagination">{{ $users->links() }}</nav>@endif</div>
@endsection


@extends('layouts.app')
@section('title',$user->exists ? 'Sửa người dùng' : 'Tạo người dùng')
@section('content')
<div class="page-wrap narrow"><div class="page-head"><div><a class="back" href="{{ route('admin.users.index') }}">← Người dùng</a><h1>{{ $user->exists ? 'Sửa tài khoản' : 'Tạo tài khoản' }}</h1></div></div>
<form method="post" action="{{ $user->exists ? route('admin.users.update',$user) : route('admin.users.store') }}" class="panel edit-form">@csrf @if($user->exists)@method('PUT')@endif
    <div class="form-grid two"><label>Họ tên<input name="name" required value="{{ old('name',$user->name) }}"></label><label>Email<input type="email" name="email" required value="{{ old('email',$user->email) }}"></label>
    <label>Mật khẩu {{ $user->exists ? '(để trống nếu giữ nguyên)' : '' }}<input type="password" name="password" {{ $user->exists ? '' : 'required' }}></label><label>Xác nhận mật khẩu<input type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }}></label>
    <label>Vai trò<select name="role" required><option value="viewer" @selected(old('role',$user->role)==='viewer')>Viewer – chỉ xem</option><option value="manager" @selected(old('role',$user->role)==='manager')>Manager – xem và chỉnh sửa</option><option value="admin" @selected(old('role',$user->role)==='admin')>Admin – toàn quyền</option></select></label><label class="check boxed"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->is_active))> Tài khoản đang hoạt động</label></div>
    <hr><h2>Quyền theo dự án</h2><p class="muted">User được xem toàn bộ căn thuộc các dự án được chọn.</p><div class="project-checks">@foreach($projects as $project)<label class="check boxed"><input type="checkbox" name="project_ids[]" value="{{ $project->id }}" @checked(in_array($project->id,(array) old('project_ids',$user->projects->pluck('id')->all())))><span><b>{{ $project->project_name }}</b><small>{{ number_format($project->properties()->count(),0,',','.') }} căn</small></span></label>@endforeach</div>
    <hr><h2>Quyền theo căn riêng</h2><p class="muted">Nhập mã căn, phân tách bằng dấu phẩy hoặc xuống dòng. Dùng khi user không được xem cả dự án.</p><label>Mã căn<textarea name="property_codes" rows="5" placeholder="VTV.3-SH17, VTV.2-SH17">{{ old('property_codes',$assignedCodes) }}</textarea></label>
    <div class="form-actions"><a class="btn ghost" href="{{ route('admin.users.index') }}">Hủy</a><button class="btn primary" type="submit">Lưu tài khoản & quyền</button></div>
</form></div>
@endsection

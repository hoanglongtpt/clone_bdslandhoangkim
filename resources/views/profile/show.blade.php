@extends('layouts.app')
@section('title', 'Hồ sơ cá nhân')
@section('content')
<div class="page-wrap profile-page">
    <section class="panel profile-panel">
        <h1>👤 Thông tin cá nhân</h1>
        <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="profile-form">
            @csrf
            @method('PUT')
            <div class="profile-identity">
                <div class="profile-avatar">
                    @if($user->avatar_path)
                        <img src="{{ route('profile.avatar') }}?v={{ $user->updated_at?->timestamp }}" alt="Ảnh đại diện">
                    @else
                        <span>♙</span>
                    @endif
                </div>
                <div><strong>{{ $user->username ?: $user->name }}</strong><span class="role-chip">{{ strtoupper($user->role) }}</span></div>
            </div>

            <label class="profile-upload">Ảnh đại diện
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                <small>Tối đa 5 MB · JPG, PNG hoặc WebP</small>
            </label>

            <div class="profile-grid">
                <label>Họ tên<input name="name" required value="{{ old('name', $user->name) }}"></label>
                <label>Username<input name="username" value="{{ old('username', $user->username) }}" placeholder="Ví dụ: trucgiang2026"></label>
                <label>Email<input type="email" name="email" required value="{{ old('email', $user->email) }}"></label>
                <label>Ngày sinh<input type="date" name="birthday" value="{{ old('birthday', $user->birthday?->format('Y-m-d')) }}"></label>
                <label>Giới tính<select name="gender"><option value="">— Chưa chọn —</option>@foreach(['Nam','Nữ','Khác'] as $gender)<option value="{{ $gender }}" @selected(old('gender',$user->gender)===$gender)>{{ $gender }}</option>@endforeach</select></label>
                <label>SĐT 1<input name="phone1" value="{{ old('phone1', $user->phone1) }}"></label>
                <label>SĐT 2<input name="phone2" value="{{ old('phone2', $user->phone2) }}"></label>
                <label>Zalo<input name="zalo" value="{{ old('zalo', $user->zalo) }}"></label>
                <label>Skype<input name="skype" value="{{ old('skype', $user->skype) }}"></label>
                <label>Facebook<input name="facebook" value="{{ old('facebook', $user->facebook) }}"></label>
                <label>Địa chỉ<input name="address" value="{{ old('address', $user->address) }}"></label>
                <label class="profile-wide">Ghi chú<textarea name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea></label>
            </div>

            <fieldset class="password-panel">
                <legend>🔑 Đổi mật khẩu</legend>
                <div class="profile-grid password-grid">
                    <label>Mật khẩu hiện tại<input type="password" name="current_password" autocomplete="current-password"></label>
                    <label>Mật khẩu mới<input type="password" name="new_password" autocomplete="new-password"></label>
                    <label>Nhập lại mật khẩu<input type="password" name="new_password_confirmation" autocomplete="new-password"></label>
                </div>
                <small>Để trống cả ba ô nếu không muốn đổi mật khẩu.</small>
            </fieldset>

            <div class="profile-actions"><button class="btn primary" type="submit">💾 Lưu thay đổi</button></div>
        </form>
    </section>
</div>
@endsection

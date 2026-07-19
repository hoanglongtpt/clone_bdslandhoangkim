<!doctype html>
<html lang="vi">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Đăng nhập · Hoàng Kim Land CRM</title><link rel="stylesheet" href="{{ asset('css/crm.css') }}"></head>
<body class="login-page">
<section class="login-card">
    <div class="login-logo"><span class="brand-mark">▥</span></div>
    <h1>Hoàng Kim Land CRM</h1>
    <p>Đăng nhập để quản lý dữ liệu bất động sản</p>
    @if($errors->any())<div class="inline-error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('login.store') }}" class="form-stack">@csrf
        <label>Email<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"></label>
        <label>Mật khẩu<input type="password" name="password" required autocomplete="current-password"></label>
        <label class="check"><input type="checkbox" name="remember" value="1"> Ghi nhớ đăng nhập</label>
        <button class="btn primary wide" type="submit">Đăng nhập</button>
    </form>
</section>
</body>
</html>


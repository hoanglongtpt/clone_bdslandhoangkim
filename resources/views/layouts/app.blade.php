<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Hoàng Kim Land CRM</title>
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ route('dashboard') }}">
        <span class="brand-mark">▥</span>
        <span>Hoàng Kim Land CRM</span>
    </a>
    <nav class="nav-links">
        <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
        <a class="{{ request()->routeIs('activities.*') ? 'active' : '' }}" href="{{ route('activities.index') }}">Hoạt động</a>
        @can('admin')<a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Người dùng</a>@endcan
        <details class="account-menu">
            <summary>♟ {{ auth()->user()->name }}</summary>
            <div class="account-popover">
                <small>{{ auth()->user()->email }}</small>
                <span class="role-chip">{{ strtoupper(auth()->user()->role) }}</span>
                <form method="post" action="{{ route('logout') }}">@csrf<button type="submit">Đăng xuất</button></form>
            </div>
        </details>
    </nav>
</header>

@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif

<main>@yield('content')</main>
<script src="{{ asset('js/crm.js') }}"></script>
@stack('scripts')
</body>
</html>


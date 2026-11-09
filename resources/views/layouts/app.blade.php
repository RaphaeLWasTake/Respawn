<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Respawn — Top Up Game')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/game-logos.css') }}">
</head>
<body>
    <header class="topbar">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">R</span> respawn<span class="brand-dot">.</span></a>
        <nav class="nav-links">
            <a href="{{ route('home') }}">Beranda</a>
            @auth
                <a href="{{ route('dashboard') }}">Dashboard</a>
                @if(auth()->user()->role === 'user')
                    <a href="{{ route('support.index') }}">Bantuan</a>
                @endif
            @endauth
        </nav>
        <div class="nav-actions">
            @auth
                <span class="nav-user"><span class="online-dot"></span>{{ auth()->user()->username }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-outline button-small">Keluar</button></form>
            @else
                <a class="nav-login" href="{{ route('login') }}">Masuk</a>
                <a class="button button-primary button-small" href="{{ route('register') }}">Daftar gratis <span>↗</span></a>
            @endauth
        </div>
    </header>
    <main>@yield('content')</main>
    <footer class="footer"><a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark">R</span> respawn<span class="brand-dot">.</span></a><span>One tap closer to your next win.</span><span>© {{ now()->year }} Respawn Store</span></footer>
</body>
</html>

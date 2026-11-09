@extends('layouts.app')
@section('title', 'Masuk — Respawn')
@section('content')
<section class="auth-wrap"><div class="auth-visual"><div class="eyebrow"><span class="spark">✳</span> WELCOME BACK, PLAYER</div><h1>Quest-mu<br><span class="gradient-text">menunggu.</span></h1><p>Masuk lagi, lanjutkan top up dan kembali ke arena.</p><div class="auth-orb">R<span>✦</span></div><div class="auth-stat"><span>01</span> YOUR GAME. YOUR RULES.</div></div>
<div class="auth-panel"><a href="{{ route('home') }}" class="back-link">← Kembali ke beranda</a><div class="auth-form-wrap"><div class="auth-kicker">PLAYER LOGIN</div><h2>Masuk akun</h2><p>Belum punya akun? <a href="{{ route('register') }}">Daftar dulu</a></p>
@include('partials.flash')
<form method="POST" action="{{ route('login.store') }}">@csrf
<label>Username atau email</label><input type="text" name="login" value="{{ old('login') }}" placeholder="contoh: player123" required autofocus autocomplete="username">
<label>Password</label><input type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
<label class="check-row"><input type="checkbox" name="remember"> Ingat saya di perangkat ini</label>
<button class="button button-primary button-full button-large">Masuk ke akun <span>↗</span></button>
</form>
<div class="auth-note" style="justify-content:center;margin:20px 0 12px">atau masuk dengan</div>
@include('partials.google-auth-button', ['label' => 'Masuk dengan Google'])
<div class="auth-note"><span>◈</span> Data akunmu terlindungi dengan aman.</div></div></div></section>
@endsection

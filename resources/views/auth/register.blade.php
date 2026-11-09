@extends('layouts.app')
@section('title', 'Daftar — Respawn')
@section('content')
<section class="auth-wrap"><div class="auth-visual"><div class="eyebrow"><span class="spark">✳</span> YOUR ADVENTURE STARTS HERE</div><h1>Satu akun.<br><span class="gradient-text">Semua game.</span></h1><p>Daftar gratis, isi ulang game favoritmu, dan fokus ke kemenangan.</p><div class="auth-orb">R<span>✦</span></div><div class="auth-stat"><span>02</span> LET'S GET YOU IN THE GAME.</div></div>
<div class="auth-panel"><a href="{{ route('home') }}" class="back-link">← Kembali ke beranda</a><div class="auth-form-wrap"><div class="auth-kicker">CREATE YOUR PLAYER ID</div><h2>Bikin akun baru</h2><p>Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
@include('partials.flash')
<form method="POST" action="{{ route('register.store') }}">@csrf
<label>Nama lengkap</label><input type="text" name="name" value="{{ old('name') }}" placeholder="Nama kamu" required autocomplete="name">
<label>Username</label><input type="text" name="username" value="{{ old('username') }}" placeholder="Pilih username unik" required autocomplete="username">
<label>Email</label><input type="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com" required autocomplete="email">
<label>Password</label><input type="password" name="password" placeholder="Minimal 8 karakter" required autocomplete="new-password">
<label>Ulangi password</label><input type="password" name="password_confirmation" placeholder="Ketik ulang password" required autocomplete="new-password">
<button class="button button-primary button-full button-large">Buat akun gratis <span>↗</span></button>
</form><div class="auth-note" style="justify-content:center;margin:20px 0 12px">atau daftar dengan</div>
@include('partials.google-auth-button', ['label' => 'Daftar dengan Google'])
<div class="auth-note"><span>◈</span> Pendaftaran otomatis membuat akun dengan role user.</div></div></div></section>
@endsection

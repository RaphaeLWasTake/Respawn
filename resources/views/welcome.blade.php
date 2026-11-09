@extends('layouts.app')
@section('title', 'Respawn — Top up game, no drama')
@section('content')
<section class="hero">
    <div class="hero-copy">
        <div class="eyebrow"><span class="spark">✳</span> LEVEL UP, TANPA RIBET</div>
        <h1>Isi ulang. <span class="gradient-text">Masuk game.</span><br>Jadi legenda.</h1>
        <p>Top up game favoritmu dalam hitungan detik. Harga transparan, proses sat-set, mabar lanjut.</p>
        <div class="hero-actions"><a href="{{ route('register') }}" class="button button-primary button-large">Mulai top up <span>↗</span></a><a href="#katalog" class="text-link">Lihat semua game <span>↓</span></a></div>
        <div class="hero-proof"><div class="avatars"><i>R</i><i>N</i><i>A</i><i>+</i></div><div><strong>12.8k+</strong><span>gamer sudah top up</span></div><span class="proof-divider"></span><div><strong>Instan</strong><span>proses kilat, 24/7</span></div></div>
    </div>
    <div class="hero-art">
        <div class="hero-glow"></div><div class="orbit orbit-one"></div><div class="orbit orbit-two"></div>
        <div class="hero-card hero-card-back"><span>✦</span><small>GENESIS CRYSTAL</small><strong>+980</strong></div>
        <div class="hero-card hero-card-main"><div class="card-top"><span>RESPAWN SELECT</span><span class="live-tag"><i></i> LIVE</span></div><div class="hero-emblem">✦</div><div class="hero-card-bottom"><span>GENSHIN IMPACT</span><strong>Rp 239.000</strong></div></div>
        <div class="floating-chip chip-fast"><span>⚡</span> Proses instan</div><div class="floating-chip chip-safe"><span>✓</span> 100% aman</div>
        <div class="hero-caption">YOUR NEXT ADVENTURE STARTS HERE <span>↗</span></div>
    </div>
</section>
<section class="trust-strip"><span><i>✦</i> HARGA JUJUR</span><span><i>◈</i> PEMBAYARAN AMAN</span><span><i>↯</i> PROSES INSTAN</span><span><i>☏</i> CS SIAGA 24/7</span></section>
<section id="katalog" class="catalog section-wrap">
    <div class="section-heading"><div><div class="eyebrow"><span class="spark">✳</span> PILIH PETUALANGANMU</div><h2>Game favorit, <span class="muted-heading">langsung gas.</span></h2><p>Pilih game-nya, pilih paketnya, lanjut push rank.</p></div><span class="catalog-count">06 GAME TERSEDIA <span>↘</span></span></div>
    <div class="game-grid">
        @foreach($games as $slug => $game)
            <a class="game-card color-{{ $game['color'] }}" href="{{ auth()->check() ? route('game.show', $slug) : route('login') }}">
                <div class="game-art game-art-image card-img-wrapper art-{{ $slug }}"><img class="game-logo" src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}" loading="lazy"><span class="art-stamp">{{ strtoupper($game['tag']) }}</span><span class="card-arrow">↗</span></div>
                <div class="game-meta"><div><span class="game-genre">{{ $game['genre'] }}</span><h3>{{ $game['name'] }}</h3></div><span class="mini-arrow">↗</span></div>
            </a>
        @endforeach
    </div>
</section>
<section class="bottom-cta section-wrap"><div class="cta-icon">✳</div><div><div class="eyebrow">SIAP NAIK LEVEL?</div><h2>Party sudah nungguin.</h2></div><a href="{{ route('register') }}" class="button button-dark">Bikin akun gratis <span>↗</span></a></section>
@endsection

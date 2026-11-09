@extends('layouts.app')
@section('title', 'Dashboard — Respawn')
@section('content')
<section class="dashboard-shell"><div class="welcome-banner"><div><div class="eyebrow"><span class="spark">✳</span> PLAYER DASHBOARD</div><h1>Yo, {{ auth()->user()->name }} <span class="wave">✌</span></h1><p>Mau main apa hari ini? Top up dulu, biar makin GG.</p></div><a class="button button-dark" href="{{ route('support.index') }}">☏ &nbsp; Butuh bantuan?</a><div class="banner-shapes">✳</div></div>
@include('partials.flash')
<div class="dash-section-head"><div><div class="eyebrow">PILIH GAME</div><h2>Gas top up, <span class="muted-heading">jagoan.</span></h2></div><span class="catalog-count">06 GAME <span>↘</span></span></div>
<div class="game-grid dashboard-grid">@foreach($games as $slug => $game)<a class="game-card color-{{ $game['color'] }}" href="{{ route('game.show', $slug) }}"><div class="game-art game-art-image card-img-wrapper art-{{ $slug }}"><img class="game-logo" src="{{ asset($game['image']) }}" alt="{{ $game['name'] }}" loading="lazy"><span class="art-stamp">{{ strtoupper($game['tag']) }}</span><span class="card-arrow">↗</span></div><div class="game-meta"><div><span class="game-genre">{{ $game['genre'] }}</span><h3>{{ $game['name'] }}</h3></div><span class="mini-arrow">↗</span></div></a>@endforeach</div>
<div class="member-strip"><span class="member-icon">✦</span><span>Login sebagai <strong>{{ auth()->user()->username }}</strong> <span class="role-pill">PLAYER</span></span><span class="member-copy">Semua game favoritmu, satu tempat.</span></div></section>
@endsection

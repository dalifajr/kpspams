@php($title = $menu['label'])
@extends('layouts.app')

@section('content')
    <div class="menu-detail">
        <div class="menu-detail__back">
            <a href="{{ $backUrl }}" class="back-link">
                <span class="material-symbols-rounded">arrow_back</span>
                <span class="back-label">{{ $menu['label'] }}</span>
            </a>
        </div>
        <header class="menu-detail__header">
            <span class="material-symbols-rounded" style="--menu-color: {{ $menu['color'] }}">{{ $menu['icon'] }}</span>
            <div>
                <p class="eyebrow">{{ $branding['community_name'] }} &middot; {{ strtoupper($user->role) }}</p>
                <h1>{{ $menu['label'] }}</h1>
                <p class="lead">
                    Halaman ini belum memiliki konten interaktif. Gunakan halaman ini sebagai placeholder
                    untuk kebutuhan pengembangan modul <strong>{{ $menu['label'] }}</strong> berikutnya.
                </p>
            </div>
        </header>
        <section class="menu-detail__body">
            <div class="placeholder-card">
                <p>Belum ada data untuk ditampilkan.</p>
                <p class="muted">Silakan sesuaikan kebutuhan modul ini pada iterasi selanjutnya.</p>
            </div>
        </section>
    </div>
    <x-bottom-nav />
@endsection

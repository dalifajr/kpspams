@php($title = 'Dashboard')
@php($bodyClass = 'page-dashboard')
@extends('layouts.app')

@section('content')
    @php($branding = $branding ?? config('kpspams.branding'))
    <div class="dashboard-screen">
        <header class="hero-card">
            <div class="hero-top">
                <div>
                    <p class="org-name">{{ $branding['community_name'] }}</p>
                    <p class="org-region">{{ $branding['region_name'] }}</p>
                </div>
                <button class="btn-switch">
                    <span class="material-symbols-rounded">sync_alt</span>
                    Switch
                </button>
            </div>
            <div class="hero-middle">
                <p class="meta">{{ $branding['app_code'] }} v. {{ $branding['app_version'] }} | <span class="user-name">{{ strtoupper($user->name) }}</span></p>
                <p class="email">{{ $user->email }}</p>
            </div>
            <div class="hero-tabs">
                <span class="tab active">Panduan</span>
                <span class="tab muted">?</span>
            </div>
        </header>

        @if ($showAdminSection)
            <section class="menu-section">
                <div class="section-title">
                    <div>
                        <p class="section-heading">Halaman Akses Admin</p>
                        <p class="section-subtitle">Panduan lengkap untuk pengurus</p>
                    </div>
                    <a href="#" class="section-link">Panduan</a>
                </div>
                <div class="menu-grid">
                    @foreach ($adminMenus as $menu)
                        <x-menu-card :menu="$menu" :number="sprintf('%02d', $loop->iteration)" />
                    @endforeach
                </div>
            </section>
        @endif

        @if (!empty($operatorMenus))
            <section class="menu-section">
                <div class="section-title">
                    <div>
                        <p class="section-heading">Halaman Akses Operator</p>
                        <p class="section-subtitle">Menu kerja petugas lapangan</p>
                    </div>
                </div>
                <div class="menu-grid">
                    @foreach ($operatorMenus as $menu)
                        <x-menu-card :menu="$menu" :number="sprintf('%02d', $showAdminSection ? $loop->iteration + count($adminMenus) : $loop->iteration)" />
                    @endforeach
                </div>
            </section>
        @endif

        @if (!empty($consumerMenus))
            <section class="menu-section">
                <div class="section-title">
                    <div>
                        <p class="section-heading">Tagihan Saya</p>
                        <p class="section-subtitle">Pantau penggunaan air secara mandiri</p>
                    </div>
                </div>
                <div class="menu-grid">
                    @foreach ($consumerMenus as $menu)
                        <x-menu-card :menu="$menu" :number="sprintf('%02d', $loop->iteration)" />
                    @endforeach
                </div>
            </section>
        @endif

        <div class="support-bar">
            <a href="{{ $branding['support_whatsapp_link'] }}" target="_blank">
                <span class="material-symbols-rounded">support_agent</span>
                <div>
                    <p class="support-label">Whatsapp (Bantuan)</p>
                    <p class="support-value">{{ $branding['support_whatsapp'] }}</p>
                </div>
            </a>
            <a href="{{ $branding['support_telegram'] }}" target="_blank">
                <span class="material-symbols-rounded">forum</span>
                <div>
                    <p class="support-label">Group Diskusi PAMS</p>
                    <p class="support-value">Telegram</p>
                </div>
            </a>
        </div>
    </div>

    <x-bottom-nav />
@endsection

@php($title = 'Golongan')
@php($bodyClass = 'page-golongan-admin')
@extends('layouts.app')

@section('content')
    <div class="golongan-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('dashboard') }}" class="chip-back" aria-label="Kembali ke dashboard">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Golongan Tarif</h1>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="golongan-list">
            @forelse ($golongans as $golongan)
                <a href="{{ route('menu.golongan.show', $golongan) }}" class="golongan-card">
                    <div class="golongan-card__badge">{{ $golongan->code }}</div>
                    <div class="golongan-card__body">
                        <div>
                            <p class="golongan-card__name">{{ $golongan->name }}</p>
                            <p class="golongan-card__meta">{{ $golongan->tariff_levels_count }} level tarif • {{ $golongan->customers_count }} pelanggan</p>
                        </div>
                        <div class="golongan-card__stats">
                            <div>
                                <span>TLM</span>
                                <strong>{{ $golongan->tariff_levels_count }}</strong>
                            </div>
                            <div>
                                <span>BNA</span>
                                <strong>{{ $golongan->non_air_fees_count }}</strong>
                            </div>
                            <div>
                                <span>Pelg.</span>
                                <strong>{{ $golongan->customers_count }}</strong>
                            </div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded">more_vert</span>
                </a>
            @empty
                <div class="placeholder-card">
                    <p>Belum ada golongan.</p>
                    <p class="muted">Tambahkan golongan baru untuk mulai mengklasifikasikan pelanggan.</p>
                </div>
            @endforelse
        </div>

        <a href="{{ route('menu.golongan.create') }}" class="fab-button">
            <span class="material-symbols-rounded">add</span>
        </a>
    </div>

    <x-bottom-nav />
@endsection

@php($title = 'Area Pelanggan')
@php($bodyClass = 'page-area-admin')
@extends('layouts.app')

@section('content')
    <div class="area-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('dashboard') }}" class="chip-back" aria-label="Kembali ke dashboard">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Area Pelanggan</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost" data-toggle-panel="area-search" aria-controls="panel-area-search" aria-expanded="false">
                        <span class="material-symbols-rounded">search</span>
                    </button>
                    <button type="button" class="icon-button ghost" data-toggle-panel="area-info" aria-controls="panel-area-info" aria-expanded="false">
                        <span class="material-symbols-rounded">info</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapsible-panel" data-panel="area-search" id="panel-area-search">
            <div class="panel-header">
                <p>Cari area</p>
                <button type="button" class="panel-close" data-panel-close="area-search">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <label class="search-field">
                <span class="material-symbols-rounded">search</span>
                <input type="text" placeholder="Cari area" oninput="window.filterAreas?.(this.value)">
            </label>
        </div>

        <div class="collapsible-panel" data-panel="area-info" id="panel-area-info">
            <div class="panel-header">
                <p>Tips area</p>
                <button type="button" class="panel-close" data-panel-close="area-info">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <p class="muted">Ikuti rekomendasi satu petugas per area agar tanggung jawab jelas dan penugasan mudah dikontrol.</p>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="area-list">
            @forelse ($areas as $area)
                <a href="{{ route('menu.area.show', $area) }}" class="area-card">
                    <div class="area-card__info">
                        <div class="area-card__avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($area->name, 0, 1)) }}</div>
                        <div>
                            <p class="area-card__eyebrow">Area</p>
                            <p class="area-card__title">{{ $area->name }}</p>
                            <p class="area-card__petugas">
                                @if ($area->petugas->isNotEmpty())
                                    @foreach ($area->petugas as $petugas)
                                        <span>{{ $petugas->name }}</span>@if (! $loop->last), @endif
                                    @endforeach
                                @else
                                    <span>Belum ada petugas</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="area-card__count">
                        <span>Pelanggan</span>
                        <strong>{{ $area->customer_count }}</strong>
                    </div>
                </a>
            @empty
                <div class="placeholder-card">
                    <p>Belum ada area yang terdaftar.</p>
                    <p class="muted">Gunakan tombol tambah untuk membuat area pelanggan baru.</p>
                </div>
            @endforelse
        </div>

        <div class="area-tip">
            <span class="material-symbols-rounded">warning</span>
            <p>Disarankan 1 area 1 petugas agar pelaporan, pencatatan meter, dan pembayaran lebih mudah dikontrol.</p>
        </div>

        <a href="{{ route('menu.area.create') }}" class="fab-button">
            <span class="material-symbols-rounded">add</span>
        </a>
    </div>

    <x-bottom-nav />
@endsection

@push('scripts')
    <script>
        window.filterAreas = (keyword = '') => {
            const term = keyword.toLowerCase().trim();
            document.querySelectorAll('.area-card').forEach((card) => {
                const text = card.textContent.toLowerCase();
                card.style.display = term === '' || text.includes(term) ? 'flex' : 'none';
            });
        };
    </script>
@endpush

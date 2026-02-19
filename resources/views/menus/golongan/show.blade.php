@php($title = 'Detail Golongan')
@php($bodyClass = 'page-golongan-admin')
@extends('layouts.app')

@section('content')
    <div class="golongan-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.golongan.index') }}" class="chip-back" aria-label="Kembali ke golongan">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>{{ $golongan->code }} • {{ $golongan->name }}</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <a href="{{ route('menu.golongan.edit', $golongan) }}" class="icon-button ghost" title="Edit golongan">
                        <span class="material-symbols-rounded">edit</span>
                    </a>
                    <form action="{{ route('menu.golongan.destroy', $golongan) }}" method="POST" onsubmit="return confirm('Hapus golongan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="icon-button ghost" title="Hapus golongan">
                            <span class="material-symbols-rounded">delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="alert-error">
                <span class="material-symbols-rounded">error</span>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <span class="material-symbols-rounded">error</span>
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        <div class="golongan-detail-card">
            <div class="golongan-detail__header">
                <div>
                    <p class="eyebrow">Golongan</p>
                    <h2>{{ $golongan->code }} — {{ $golongan->name }}</h2>
                    <p class="muted">Tarif ditetapkan berdasarkan level meter air.</p>
                </div>
                <div class="golongan-detail__badge">{{ $golongan->customers_count }} Pelanggan</div>
            </div>
        </div>

        <div class="golongan-tabs" data-tabs>
            <div class="golongan-tabs__nav">
                <button type="button" data-tab-target="tariffs" class="is-active">Tarif Level Meter</button>
                <button type="button" data-tab-target="fees">Biaya Non Air</button>
            </div>
            <div class="golongan-tabs__content is-active" data-tab-content="tariffs">
                <div class="tariff-list">
                    @forelse ($golongan->tariffLevels as $tariff)
                        <div class="tariff-row">
                            <div>
                                <p class="tariff-range">Posisi Meter {{ rtrim(rtrim(number_format($tariff->meter_start, 2), '0'), '.') }} -
                                    {{ $tariff->meter_end !== null ? rtrim(rtrim(number_format($tariff->meter_end, 2), '0'), '.') : '∞' }}</p>
                                <p class="tariff-price">Rp {{ number_format($tariff->price, 0, ',', '.') }}</p>
                            </div>
                            <form action="{{ route('menu.golongan.tariffs.destroy', [$golongan, $tariff]) }}" method="POST" onsubmit="return confirm('Hapus tarif ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-button danger" title="Hapus tarif">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="muted">Belum ada tarif level meter.</p>
                    @endforelse
                </div>
                <form action="{{ route('menu.golongan.tariffs.store', $golongan) }}" method="POST" class="tariff-inline-form">
                    @csrf
                    <label>
                        <span>Posisi Awal</span>
                        <input type="number" name="meter_start" min="0" step="0.1" required>
                    </label>
                    <label>
                        <span>Posisi Akhir</span>
                        <input type="number" name="meter_end" min="0" step="0.1">
                    </label>
                    <label>
                        <span>Harga / m³</span>
                        <input type="number" name="price" min="0" step="100" required>
                    </label>
                    <button type="submit" class="icon-button primary" title="Tambah tarif">
                        <span class="material-symbols-rounded">add</span>
                    </button>
                </form>
            </div>
            <div class="golongan-tabs__content" data-tab-content="fees">
                <div class="tariff-list">
                    @forelse ($golongan->nonAirFees as $fee)
                        <div class="tariff-row">
                            <div>
                                <p class="tariff-range">{{ $fee->label }}</p>
                                <p class="tariff-price">Rp {{ number_format($fee->price, 0, ',', '.') }}</p>
                            </div>
                            <form action="{{ route('menu.golongan.fees.destroy', [$golongan, $fee]) }}" method="POST" onsubmit="return confirm('Hapus biaya ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-button danger" title="Hapus biaya">
                                    <span class="material-symbols-rounded">close</span>
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="muted">Belum ada biaya non air.</p>
                    @endforelse
                </div>
                <form action="{{ route('menu.golongan.fees.store', $golongan) }}" method="POST" class="tariff-inline-form">
                    @csrf
                    <label>
                        <span>Nama Biaya</span>
                        <input type="text" name="label" required>
                    </label>
                    <label>
                        <span>Nominal</span>
                        <input type="number" name="price" min="0" step="100" required>
                    </label>
                    <button type="submit" class="icon-button primary" title="Tambah biaya">
                        <span class="material-symbols-rounded">add</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <x-bottom-nav />
@endsection

@php($title = 'Detail Area')
@php($bodyClass = 'page-area-admin')
@extends('layouts.app')

@section('content')
    <div class="area-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.area') }}" class="chip-back" aria-label="Kembali ke daftar area">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>{{ $area->name }}</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost" data-toggle-panel="quick-actions" aria-controls="panel-quick-actions" aria-expanded="false">
                        <span class="material-symbols-rounded">more_horiz</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapsible-panel" data-panel="quick-actions" id="panel-quick-actions">
            <div class="panel-header">
                <p>Pintasan</p>
                <button type="button" class="panel-close" data-panel-close="quick-actions">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="quick-actions">
                <a href="{{ route('menu.area.edit', $area) }}" class="btn-secondary light">Edit</a>
                <form action="{{ route('menu.area.destroy', $area) }}" method="POST" onsubmit="return confirm('Hapus area ini? Data relasi petugas akan hilang.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">Hapus Area</button>
                </form>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="area-detail-card">
            <div>
                <p class="area-card__eyebrow">Nama Area</p>
                <p class="area-detail__title">{{ $area->name }}</p>
                <p class="muted">Pelanggan <strong>{{ $area->customer_count }}</strong></p>
            </div>
            <div class="area-detail__actions">
                <a href="{{ route('menu.area.edit', $area) }}" class="icon-button primary">
                    <span class="material-symbols-rounded">edit</span>
                </a>
                <form action="{{ route('menu.area.destroy', $area) }}" method="POST" onsubmit="return confirm('Hapus area ini? Data relasi petugas akan hilang.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="icon-button danger">
                        <span class="material-symbols-rounded">delete</span>
                    </button>
                </form>
            </div>
        </div>

        <div class="area-detail-card">
            <div class="card-header">
                <div>
                    <p class="eyebrow">Set Penugasan Petugas</p>
                    <h2>Petugas Area</h2>
                </div>
            </div>
            <div class="petugas-chip-list">
                @forelse ($area->petugas as $petugas)
                    <div class="petugas-chip">
                        <span class="material-symbols-rounded">person</span>
                        <span>{{ $petugas->name }}</span>
                        <form action="{{ route('menu.area.petugas.remove', [$area, $petugas]) }}" method="POST" onsubmit="return confirm('Hapus petugas ini dari area?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-button danger small">
                                <span class="material-symbols-rounded">delete</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="muted">Belum ada petugas pada area ini.</p>
                @endforelse
            </div>
            @if ($petugasOptions->isEmpty())
                <p class="muted">Semua petugas telah ditugaskan ke area ini.</p>
            @else
                <form action="{{ route('menu.area.petugas.assign', $area) }}" method="POST" class="petugas-form">
                    @csrf
                    <label class="form-field">
                        <span>Pilih Petugas</span>
                        <select name="user_id" required>
                            <option value="" selected disabled>Pilih petugas</option>
                            @foreach ($petugasOptions as $option)
                                <option value="{{ $option->id }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <button type="submit" class="btn-primary">
                        <span>Tambah Petugas</span>
                    </button>
                </form>
            @endif
        </div>

        <a href="{{ route('menu.area') }}" class="btn-secondary light full-width">Kembali ke daftar area</a>
    </div>

    <x-bottom-nav />
@endsection

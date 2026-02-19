@php($title = 'Tambah Area')
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
                        <h1>Area Baru</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost" data-toggle-panel="area-guide" aria-controls="panel-area-guide" aria-expanded="false">
                        <span class="material-symbols-rounded">help</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapsible-panel" data-panel="area-guide" id="panel-area-guide">
            <div class="panel-header">
                <p>Panduan</p>
                <button type="button" class="panel-close" data-panel-close="area-guide">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <p class="muted">Gunakan nama area yang mudah dikenali oleh petugas dan pelanggan. Jumlah pelanggan dapat dikoreksi kapan saja.</p>
        </div>

        <div class="area-detail-card">
            @include('menus.area.partials.form', [
                'action' => route('menu.area.store'),
                'submitLabel' => 'Simpan Area',
                'area' => $area ?? null,
            ])
        </div>
    </div>

    <x-bottom-nav />
@endsection

@php($title = 'Edit Area')
@php($bodyClass = 'page-area-admin')
@extends('layouts.app')

@section('content')
    <div class="area-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.area.show', $area) }}" class="chip-back" aria-label="Kembali ke detail area">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Edit Area</h1>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost" data-toggle-panel="edit-help" aria-controls="panel-edit-help" aria-expanded="false">
                        <span class="material-symbols-rounded">tips_and_updates</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="collapsible-panel" data-panel="edit-help" id="panel-edit-help">
            <div class="panel-header">
                <p>Saran</p>
                <button type="button" class="panel-close" data-panel-close="edit-help">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <p class="muted">Perbarui catatan area ketika ada perubahan jumlah pelanggan atau rotasi petugas.</p>
        </div>

        <div class="area-detail-card">
            @include('menus.area.partials.form', [
                'action' => route('menu.area.update', $area),
                'method' => 'PUT',
                'submitLabel' => 'Perbarui Area',
                'area' => $area,
            ])
        </div>
    </div>

    <x-bottom-nav />
@endsection

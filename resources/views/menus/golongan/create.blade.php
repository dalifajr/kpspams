@php($title = 'Tambah Golongan')
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
                        <h1>Tambah Golongan</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="golongan-detail-card">
            @include('menus.golongan.partials.form', [
                'action' => route('menu.golongan.store'),
                'method' => 'POST',
                'golongan' => null,
            ])
        </div>
    </div>

    <x-bottom-nav />
@endsection

@php($title = 'Edit Golongan')
@php($bodyClass = 'page-golongan-admin')
@extends('layouts.app')

@section('content')
    <div class="golongan-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('menu.golongan.show', $golongan) }}" class="chip-back" aria-label="Kembali ke detail golongan">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Edit {{ $golongan->name }}</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="golongan-detail-card">
            @include('menus.golongan.partials.form', [
                'action' => route('menu.golongan.update', $golongan),
                'method' => 'PUT',
                'golongan' => $golongan,
            ])
        </div>
    </div>

    <x-bottom-nav />
@endsection

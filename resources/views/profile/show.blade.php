@php($title = 'Profil Saya')
@extends('layouts.app')

@section('content')
    <div class="profile-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="chip-back" aria-label="Kembali">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Profil Saya</h1>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-avatar">
                <span class="material-symbols-rounded">person</span>
            </div>
            <h2>{{ $user->name }}</h2>
            <p class="muted">{{ ucfirst($user->role) }} • {{ $user->status === \App\Models\User::STATUS_APPROVED ? 'Aktif' : 'Pending' }}</p>
        </div>

        <div class="profile-info">
            <div>
                <p class="eyebrow">Kontak</p>
                <dl>
                    <div>
                        <dt>Email</dt>
                        <dd>{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt>Nomor HP</dt>
                        <dd>{{ $user->phone_number }}</dd>
                    </div>
                </dl>
            </div>
            <div>
                <p class="eyebrow">Area &amp; Alamat</p>
                <dl>
                    <div>
                        <dt>Area Penugasan</dt>
                        <dd>{{ $user->area ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt>Alamat Singkat</dt>
                        <dd>{{ $user->address_short ?? '-' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <x-bottom-nav />
@endsection

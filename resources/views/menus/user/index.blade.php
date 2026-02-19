@php($title = 'Data User')
@php($bodyClass = 'page-user-admin')
@extends('layouts.app')

@php($hasSearch = $search !== '')
@php($filtersActive = $filters['role'] !== 'all' || $filters['sort'] !== 'az' || $filters['group'] !== 'all' || $filters['area'] !== 'all')
@section('content')
    <div class="user-admin">
        <div class="menu-head-sticky" data-menu-header data-panel-root>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('dashboard') }}" class="chip-back" aria-label="Kembali ke dashboard">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Data User</h1>
                    </div>
                </div>
                @if ($tab !== 'confirm')
                    <div class="toolbar-actions">
                        <button type="button" class="icon-button ghost {{ $hasSearch ? 'is-active' : '' }}" data-toggle-panel="search" aria-expanded="{{ $hasSearch ? 'true' : 'false' }}" aria-controls="user-search-panel">
                            <span class="material-symbols-rounded">search</span>
                        </button>
                        <button type="button" class="icon-button ghost {{ $filtersActive ? 'is-active' : '' }}" data-toggle-panel="filters" aria-expanded="{{ $filtersActive ? 'true' : 'false' }}" aria-controls="user-filter-panel">
                            <span class="material-symbols-rounded">tune</span>
                        </button>
                    </div>
                @endif
            </div>

            <div class="user-admin__tabs">
                <a href="{{ route('menu.user', ['tab' => 'data']) }}" class="tab {{ $tab === 'confirm' ? '' : 'active' }}">Data User</a>
                <a href="{{ route('menu.user', ['tab' => 'confirm']) }}" class="tab {{ $tab === 'confirm' ? 'active' : '' }}">
                    User Konfirmasi
                    @if ($pendingUsers->count())
                        <span class="badge">{{ $pendingUsers->count() }}</span>
                    @endif
                </a>
            </div>

            @if ($tab !== 'confirm')
                <form class="user-filter-form" action="{{ route('menu.user') }}" method="GET" id="user-filter-form">
                    <input type="hidden" name="tab" value="data">

                    <div class="collapsible-panel {{ $hasSearch ? 'is-open' : '' }}" data-panel="search" id="user-search-panel">
                        <div class="panel-header">
                            <p>Pencarian Cepat</p>
                            <button type="button" class="panel-close" data-panel-close="search">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <label class="search-field">
                            <span class="material-symbols-rounded">search</span>
                            <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, email, atau nomor" autocomplete="off">
                            <button type="submit" class="icon-button primary compact" title="Cari">
                                <span class="material-symbols-rounded">arrow_right_alt</span>
                            </button>
                        </label>
                    </div>

                    <div class="collapsible-panel {{ $filtersActive ? 'is-open' : '' }}" data-panel="filters" id="user-filter-panel">
                        <div class="panel-header">
                            <p>Penyaring Data</p>
                            <button type="button" class="panel-close" data-panel-close="filters">
                                <span class="material-symbols-rounded">close</span>
                            </button>
                        </div>
                        <div class="filter-row">
                            <label class="filter-control">
                                <span>Role</span>
                                <select name="role" onchange="this.form.submit()">
                                    <option value="all" @selected($filters['role'] === 'all')>Semua</option>
                                    <option value="user" @selected($filters['role'] === 'user')>User</option>
                                    <option value="petugas" @selected($filters['role'] === 'petugas')>Petugas</option>
                                </select>
                            </label>
                            <label class="filter-control">
                                <span>Urutkan</span>
                                <select name="sort" onchange="this.form.submit()">
                                    <option value="az" @selected($filters['sort'] === 'az')>A - Z</option>
                                    <option value="za" @selected($filters['sort'] === 'za')>Z - A</option>
                                </select>
                            </label>
                            <input type="hidden" name="group" value="all">
                            <label class="filter-control">
                                <span>Area</span>
                                <select name="area" onchange="this.form.submit()">
                                    <option value="all" @selected($filters['area'] === 'all')>Semua</option>
                                    @foreach ($areas as $areaOption)
                                        @php($petugasNames = $areaOption->petugas->pluck('name')->implode(', '))
                                        <option value="{{ $areaOption->id }}" @selected((string) $filters['area'] === (string) $areaOption->id)>
                                            {{ $areaOption->name }}{{ $petugasNames ? ' — ' . $petugasNames : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                </form>
            @endif
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        @if ($tab === 'confirm')
            <div class="pending-list">
                @forelse ($pendingUsers as $pending)
                    @php($avatarUrl = $pending->avatar_path ? \Illuminate\Support\Facades\Storage::url($pending->avatar_path) : null)
                    <div class="pending-card">
                        <div class="pending-card__info">
                            <div class="user-card__avatar" style="--avatar-color: #2563eb">
                                @if ($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="{{ $pending->name }}">
                                @else
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($pending->name, 0, 1)) }}
                                @endif
                            </div>
                            <div>
                                <p class="user-card__name">{{ $pending->name }}</p>
                                <p class="user-card__address">{{ $pending->phone_number }}</p>
                                <p class="user-card__tag">Area {{ $pending->area ?? 'Belum diisi' }}</p>
                            </div>
                        </div>
                        <div class="pending-card__actions">
                            <form action="{{ route('menu.user.approve', $pending) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-primary">Setujui</button>
                            </form>
                            <form action="{{ route('menu.user.approve', $pending) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="notify" value="1">
                                <button type="submit" class="btn-secondary">Setujui &amp; WhatsApp</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="placeholder-card">
                        <p>Tidak ada user yang menunggu konfirmasi.</p>
                        <p class="muted">Pengguna baru akan tampil di sini jika registrasi membutuhkan persetujuan admin.</p>
                    </div>
                @endforelse
            </div>
        @else
            <p class="result-count">{{ $users->count() }} hasil</p>

            <div class="user-list">
                @forelse ($users as $managed)
                    @php($avatarUrl = $managed->avatar_path ? \Illuminate\Support\Facades\Storage::url($managed->avatar_path) : null)
                    <a href="{{ route('menu.user.show', $managed) }}" class="user-card">
                        <div class="user-card__avatar" style="--avatar-color: {{ $managed->isPetugas() ? '#0f9d58' : '#2563eb' }}">
                            @if ($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $managed->name }}">
                            @else
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($managed->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="user-card__info">
                            <p class="user-card__name">{{ $managed->name }}</p>
                            <p class="user-card__address">{{ $managed->address_short ?? 'Alamat belum diisi' }}</p>
                            <p class="user-card__tag">{{ $managed->isPetugas() ? 'Petugas' : 'User' }}</p>
                        </div>
                        <span class="material-symbols-rounded user-card__chevron">chevron_right</span>
                    </a>
                @empty
                    <div class="placeholder-card">
                        <p>Belum ada user yang terdaftar.</p>
                        <p class="muted">Gunakan tombol tambah pengguna untuk memasukkan akun baru.</p>
                    </div>
                @endforelse
            </div>
        @endif

        <a href="{{ route('menu.user.create') }}" class="fab-button">
            <span class="material-symbols-rounded">add</span>
        </a>
    </div>

    <x-bottom-nav />
@endsection

@if (session('whatsapp_link'))
    @push('scripts')
        <script>
            window.open('{{ session('whatsapp_link') }}', '_blank');
        </script>
    @endpush
@endif

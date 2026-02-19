@php($title = 'Data Pelanggan')
@php($bodyClass = 'page-customer-admin')
@extends('layouts.app')

@php($areas = $areas ?? collect())
@php($areaFilter = $filters['area'] ?? 'all')
@php($search = $filters['name'] ?? '')
@php($hasSearch = $search !== '')
@php($areaTotals = $stats['areaTotals'] ?? collect())

@section('content')
    <div class="customer-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar customer-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('dashboard') }}" class="chip-back" aria-label="Kembali ke dashboard">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Data Pelanggan</h1>
                        <span class="title-pill">{{ $stats['total'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="toolbar-actions">
                    <button type="button" class="icon-button ghost {{ $hasSearch ? 'is-active' : '' }}" data-toggle-panel="search" aria-expanded="{{ $hasSearch ? 'true' : 'false' }}" aria-controls="customer-search-panel" title="Cari pelanggan">
                        <span class="material-symbols-rounded">search</span>
                    </button>
                    <button type="button" class="icon-button ghost" title="Unduh PDF" disabled>
                        <span class="material-symbols-rounded">picture_as_pdf</span>
                    </button>
                    <button type="button" class="icon-button ghost" title="Unduh Excel" disabled>
                        <span class="material-symbols-rounded">grid_on</span>
                    </button>
                </div>
            </div>

            <form action="{{ route('menu.customers.index') }}" method="GET" class="customer-search-form">
                <input type="hidden" name="area" value="{{ $areaFilter }}">
                <div class="collapsible-panel {{ $hasSearch ? 'is-open' : '' }}" data-panel="search" id="customer-search-panel">
                    <div class="panel-header">
                        <p>Pencarian Pelanggan</p>
                        <button type="button" class="panel-close" data-panel-close="search">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <label class="search-field">
                        <span class="material-symbols-rounded">search</span>
                        <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama, kode pelanggan, atau alamat" autocomplete="off">
                        <button type="submit" class="icon-button primary compact" title="Cari">
                            <span class="material-symbols-rounded">arrow_right_alt</span>
                        </button>
                    </label>
                </div>
            </form>

            <div class="customer-chip-row">
                @php($queryBase = $search ? ['q' => $search] : [])
                <a href="{{ route('menu.customers.index', array_merge($queryBase, ['area' => 'all'])) }}" class="chip-pill {{ $areaFilter === 'all' ? 'is-active' : '' }}">Semua ({{ $stats['total'] ?? 0 }})</a>
                @foreach ($areas as $area)
                    @php($count = $areaTotals[$area->id] ?? 0)
                    <a href="{{ route('menu.customers.index', array_merge($queryBase, ['area' => $area->id])) }}" class="chip-pill {{ (string) $areaFilter === (string) $area->id ? 'is-active' : '' }}">
                        {{ $area->name }} ({{ $count }})
                    </a>
                @endforeach
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="customer-list">
            @forelse ($customers as $customer)
                @php($areaInitial = $customer->area ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($customer->area->name, 0, 1)) : '-')
                <a href="{{ route('menu.customers.show', $customer) }}" class="customer-card">
                    <div class="customer-card__order">
                        <span>{{ $loop->iteration }}</span>
                        <small>{{ $customer->customer_code }}</small>
                    </div>
                    <div class="customer-card__body">
                        <div>
                            <p class="customer-card__name">{{ $customer->name }}</p>
                            <p class="customer-card__address">{{ $customer->address_short }}</p>
                            <p class="customer-card__family">{{ $customer->family_members }} Orang</p>
                        </div>
                        <div class="customer-card__meta">
                            <div class="badge-tile">
                                <span>Area</span>
                                <strong>{{ $areaInitial }}</strong>
                            </div>
                            <div class="badge-tile">
                                <span>Gol</span>
                                <strong>{{ optional($customer->golongan)->code ?? '-' }}</strong>
                            </div>
                            <div class="badge-tile">
                                <span>Kondisi</span>
                                <strong>100%</strong>
                            </div>
                        </div>
                    </div>
                    <span class="material-symbols-rounded customer-card__chevron">chevron_right</span>
                </a>
            @empty
                <div class="placeholder-card">
                    <p>Belum ada data pelanggan.</p>
                    <p class="muted">Gunakan tombol tambah untuk memasukkan pelanggan baru.</p>
                </div>
            @endforelse
        </div>

        <p class="muted small">{{ $customers->count() }} pelanggan ditampilkan.</p>

        <a href="{{ route('menu.customers.create') }}" class="fab-button">
            <span class="material-symbols-rounded">add</span>
        </a>
    </div>

    <x-bottom-nav />
@endsection

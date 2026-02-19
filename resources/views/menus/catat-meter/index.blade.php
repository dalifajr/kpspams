@php($title = 'Catat Meter')
@extends('layouts.app')

@section('content')
    <div class="meter-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('dashboard') }}" class="chip-back" aria-label="Kembali ke dashboard">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Catat Meter</h1>
                        <form action="{{ route('catat-meter.index') }}" method="GET" class="year-switcher">
                            <label class="sr-only" for="year-select">Pilih tahun</label>
                            <select id="year-select" name="year" onchange="this.form.submit()">
                                @foreach ($yearOptions as $optionYear)
                                    <option value="{{ $optionYear }}" @selected($optionYear === $year)>{{ $optionYear }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>
                @if (auth()->user()->isAdmin())
                    <button type="button" class="icon-button primary" data-toggle-panel="new-period" aria-expanded="false" aria-controls="new-period-panel">
                        <span class="material-symbols-rounded">add</span>
                    </button>
                @endif
            </div>

            @if (auth()->user()->isAdmin())
                <div class="collapsible-panel" data-panel="new-period" id="new-period-panel">
                    <div class="panel-header">
                        <p>Buka Periode Catat Meter</p>
                        <button type="button" class="panel-close" data-panel-close="new-period">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>
                    <form action="{{ route('catat-meter.store') }}" method="POST" class="new-period-form">
                        @csrf
                        <div class="form-grid">
                            <label class="form-field">
                                <span>Bulan</span>
                                <select name="month">
                                    @foreach ($monthOptions as $optionValue => $label)
                                        <option value="{{ $optionValue }}" @selected($optionValue === ($nextPeriod['month'] ?? now()->month))>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="form-field">
                                <span>Tahun</span>
                                <input type="number" name="year" value="{{ $nextPeriod['year'] ?? $year }}" min="2020" max="2100">
                            </label>
                        </div>
                        <label class="form-field">
                            <span>Catatan</span>
                            <textarea name="notes" rows="2" placeholder="Keterangan penugasan (opsional)"></textarea>
                        </label>
                        <div class="form-actions">
                            <button type="submit" class="btn-primary">Buka Periode</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="meter-period-grid">
            @forelse ($periods as $period)
                @php($monthName = \Illuminate\Support\Carbon::create(null, $period->month, 1)->translatedFormat('F'))
                <a href="{{ route('catat-meter.show', $period) }}" class="meter-period-card">
                    <div class="meter-period-card__month">
                        <span class="meter-period-card__month-number">{{ str_pad($period->month, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <p class="meter-period-card__month-name">{{ $monthName }}</p>
                            <p class="meter-period-card__status">{{ ucfirst($period->status) }}</p>
                        </div>
                    </div>
                    <p class="meter-period-card__area">{{ $period->assignment_count }} area • {{ $period->summary['target'] }} pelanggan</p>
                    <div class="meter-progress">
                        <div class="meter-progress__bar">
                            <span style="width: {{ $period->summary['progress'] }}%"></span>
                        </div>
                        <p>{{ $period->summary['completed'] }} selesai • {{ $period->summary['pending'] }} pending</p>
                    </div>
                    <div class="meter-period-card__stats">
                        <div>
                            <span>Volume</span>
                            <strong>{{ number_format($period->summary['volume'], 2) }} m³</strong>
                        </div>
                        <div>
                            <span>Tagihan</span>
                            <strong>Rp {{ number_format($period->summary['bill'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </a>
            @empty
                <div class="placeholder-card">
                    <p>Belum ada periode catat meter pada tahun ini.</p>
                    <p class="muted">Admin dapat membuka periode baru dengan tombol tambah.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

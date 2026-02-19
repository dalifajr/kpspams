@php
    use Illuminate\Support\Carbon;
    use Illuminate\Support\Facades\Storage;
    $title = 'Catat Meter - ' . Carbon::create(null, $period->month, 1)->translatedFormat('F Y');
    $baseShowRoute = route('catat-meter.show', $period);
@endphp
@extends('layouts.app')

@section('content')
    @php
        $activeAreaLabel = $activeAreaId ? optional($areas->firstWhere('id', $activeAreaId))->name : 'Semua area';
        $exportParams = ['meterPeriod' => $period];
        $pendingRouteParams = ['meterPeriod' => $period];
        if ($activeAreaId) {
            $exportParams['area'] = $activeAreaId;
            $pendingRouteParams['area'] = $activeAreaId;
        }
        $pendingUrl = route('catat-meter.pending', $pendingRouteParams);
    @endphp
    <div class="meter-detail-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('catat-meter.index', ['year' => $period->year]) }}" class="chip-back" aria-label="Kembali ke daftar catat meter">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>{{ Carbon::create(null, $period->month, 1)->translatedFormat('F Y') }}</h1>
                    </div>
                </div>
            </div>
            <div class="meter-header-actions">
                <div class="meter-area-chips">
                    <a href="{{ $baseShowRoute }}" class="meter-area-chip {{ $activeAreaId ? '' : 'is-active' }}">Semua</a>
                    @foreach ($areas as $area)
                        <a href="{{ $baseShowRoute . '?area=' . $area->id }}" class="meter-area-chip {{ $activeAreaId === $area->id ? 'is-active' : '' }}">
                            {{ $area->name }}
                        </a>
                    @endforeach
                </div>
                <div class="meter-header-buttons">
                    <a href="{{ $pendingUrl }}" class="icon-button ghost" title="Catat pelanggan">
                        <span class="material-symbols-rounded" aria-hidden="true">groups</span>
                        <span class="sr-only">Buka daftar pelanggan belum dicatat</span>
                        @if ($pendingReadings->isNotEmpty())
                            <span class="badge">{{ $pendingReadings->count() }}</span>
                        @endif
                    </a>
                    <a href="{{ route('catat-meter.export.pdf', $exportParams) }}" target="_blank" rel="noopener" class="icon-button ghost" title="Cetak PDF">
                        <span class="material-symbols-rounded" aria-hidden="true">picture_as_pdf</span>
                        <span class="sr-only">Cetak PDF</span>
                    </a>
                    <a href="{{ route('catat-meter.export.excel', $exportParams) }}" class="icon-button primary" title="Unduh Excel">
                        <span class="material-symbols-rounded" aria-hidden="true">download</span>
                        <span class="sr-only">Unduh Excel</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="meter-summary-pill">
            <header>
                <p class="meter-summary-pill__label">{{ $activeAreaLabel }}</p>
                <h2>{{ $summary['completed'] }} / {{ $summary['target'] }} pelanggan</h2>
            </header>
            <div class="meter-summary-pill__stats">
                <div>
                    <span>Volume</span>
                    <strong>{{ number_format($summary['volume'], 2) }} m³</strong>
                </div>
                <div>
                    <span>Tagihan</span>
                    <strong>Rp {{ number_format($summary['bill'], 0, ',', '.') }}</strong>
                </div>
            </div>
            <div class="meter-summary-pill__progress">
                <div class="meter-progress__bar">
                    <span style="width: {{ $summary['progress'] }}%"></span>
                </div>
                <p>{{ $summary['completed'] }} selesai • {{ $summary['pending'] }} pending</p>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="meter-reading-list">
            @forelse ($completedReadings as $reading)
                <article class="meter-reading-card is-complete">
                    <header>
                        <div>
                            <p class="meter-reading-card__code">{{ $reading->customer->customer_code ?? '----' }}</p>
                            <h2>{{ $reading->customer->name }}</h2>
                            <p class="meter-reading-card__meta">Area {{ optional($reading->area)->name }} • Petugas {{ optional($reading->petugas)->name ?? '-' }}</p>
                        </div>
                        <span class="status-chip is-unpaid">Belum Bayar</span>
                    </header>
                    <div class="meter-reading-card__body">
                        <dl class="meter-reading-card__stats">
                            <div>
                                <dt>Angka Bulan Lalu</dt>
                                <dd>{{ $reading->start_reading !== null ? number_format($reading->start_reading, 2) . ' m³' : '0 m³' }}</dd>
                            </div>
                            <div>
                                <dt>Meter Bulan Ini</dt>
                                <dd>{{ $reading->end_reading !== null ? number_format($reading->end_reading, 2) . ' m³' : '-' }}</dd>
                            </div>
                            <div>
                                <dt>Volume Pemakaian</dt>
                                <dd>{{ number_format($reading->usage_m3, 2) }} m³</dd>
                            </div>
                            <div>
                                <dt>Tagihan</dt>
                                <dd>Rp {{ number_format($reading->bill_amount, 0, ',', '.') }}</dd>
                            </div>
                        </dl>
                        @if ($reading->note)
                            <p class="meter-reading-card__note">{{ $reading->note }}</p>
                        @endif
                    </div>
                    <footer class="meter-reading-card__footer">
                        <span class="meter-reading-card__time">Dicatat {{ optional($reading->recorded_at)->diffForHumans() }}</span>
                        @if ($reading->photo_path)
                            <a href="{{ Storage::url($reading->photo_path) }}" target="_blank" rel="noopener" class="meter-reading-card__photo">Lihat bukti</a>
                        @endif
                    </footer>
                </article>
            @empty
                <div class="placeholder-card">
                    <p>Belum ada pelanggan selesai dicatat untuk filter ini.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

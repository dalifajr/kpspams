@php
    use Illuminate\Support\Carbon;
    $title = 'Catat Meter - Pelanggan Belum Dicatat';
    $basePendingRoute = route('catat-meter.pending', $period);
    $activeAreaLabel = $activeAreaId ? optional($areas->firstWhere('id', $activeAreaId))->name : 'Semua area';
    $completedRouteParams = ['meterPeriod' => $period];
    if ($activeAreaId) {
        $completedRouteParams['area'] = $activeAreaId;
    }
@endphp
@extends('layouts.app')

@section('content')
    <div class="meter-detail-screen" data-panel-root>
        <div class="menu-head-sticky" data-menu-header>
            <div class="menu-toolbar">
                <div class="toolbar-leading">
                    <a href="{{ route('catat-meter.show', $completedRouteParams) }}" class="chip-back" aria-label="Kembali ke rekap periode">
                        <span class="material-symbols-rounded" aria-hidden="true">arrow_back</span>
                    </a>
                    <div class="toolbar-title">
                        <h1>Belum Dicatat</h1>
                        <span class="title-pill">{{ Carbon::create(null, $period->month, 1)->translatedFormat('F Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="meter-header-actions">
                <div class="meter-area-chips">
                    <a href="{{ $basePendingRoute }}" class="meter-area-chip {{ $activeAreaId ? '' : 'is-active' }}">Semua</a>
                    @foreach ($areas as $area)
                        <a href="{{ $basePendingRoute . '?area=' . $area->id }}" class="meter-area-chip {{ $activeAreaId === $area->id ? 'is-active' : '' }}">
                            {{ $area->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pending-page-head">
            <div>
                <p class="pending-page-label">Filter aktif</p>
                <h2>{{ $activeAreaLabel }}</h2>
            </div>
            <p class="pending-page-count">{{ $pendingReadings->count() }} pelanggan menunggu dicatat</p>
        </div>

        @if (session('status'))
            <div class="alert-success">
                <span class="material-symbols-rounded">check_circle</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <div class="pending-list">
            @forelse ($pendingReadings as $pending)
                <article class="pending-customer">
                    <header class="pending-customer__header">
                        <div>
                            <p class="pending-customer__name">{{ $pending->customer->name }}</p>
                            <p class="pending-customer__meta">{{ $pending->customer->customer_code ?? '----' }} • Area {{ optional($pending->area)->name }}</p>
                        </div>
                        <span class="pending-customer__chip">Awal {{ $pending->start_reading !== null ? number_format($pending->start_reading, 2) : '0.00' }} m³</span>
                    </header>
                    <div class="pending-customer__body">
                        <dl class="pending-customer__details">
                            <div>
                                <dt>Alamat</dt>
                                <dd>{{ $pending->customer->address_short ?? 'Belum diisi' }}</dd>
                            </div>
                            <div>
                                <dt>Golongan</dt>
                                <dd>{{ optional($pending->customer->golongan)->name ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt>Kontak</dt>
                                <dd>{{ $pending->customer->phone_number ?? '-' }}</dd>
                            </div>
                        </dl>
                        @php
                            $formAction = route('catat-meter.readings.update', [$period, $pending]);
                            if ($activeAreaId) {
                                $formAction .= '?area=' . $activeAreaId;
                            }
                        @endphp
                        <form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="pending-reading-form" data-meter-form data-start-reading="{{ $pending->start_reading ?? 0 }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="start_reading" value="{{ $pending->start_reading ?? 0 }}">
                            <input type="hidden" name="mark_complete" value="1">
                            <input type="hidden" name="redirect_to" value="pending">
                            @if ($activeAreaId)
                                <input type="hidden" name="area" value="{{ $activeAreaId }}">
                            @endif
                            <div class="pending-reading-form__grid">
                                <div class="read-only-field">
                                    <span>Angka bulan lalu</span>
                                    <strong>{{ $pending->start_reading !== null ? number_format($pending->start_reading, 2) . ' m³' : '0 m³' }}</strong>
                                </div>
                                <label class="form-field">
                                    <span>Meter bulan ini</span>
                                    <input type="number" name="end_reading" step="0.01" min="0" required data-end-reading value="{{ old('end_reading') }}">
                                </label>
                            </div>
                            <label class="upload-field">
                                <span>Foto bukti meteran (opsional)</span>
                                <input type="file" name="photo" accept="image/*">
                            </label>
                            <div class="meter-form-volume">
                                <span>Volume bulan ini</span>
                                <strong data-usage-output>0 m³</strong>
                            </div>
                            <label class="form-field">
                                <span>Catatan (opsional)</span>
                                <textarea name="note" rows="2" placeholder="Tambahkan catatan lapangan jika diperlukan">{{ old('note') }}</textarea>
                            </label>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Simpan dan tandai selesai</button>
                            </div>
                        </form>
                    </div>
                </article>
            @empty
                <div class="placeholder-card">
                    <p>Semua pelanggan pada filter ini sudah dicatat.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

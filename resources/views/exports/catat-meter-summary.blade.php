@php
    use Illuminate\Support\Carbon;
    $periodLabel = Carbon::create(null, $period->month, 1)->translatedFormat('F Y');
    $exportTime = now()->format('d/m/Y H:i');
    $activeArea = $activeAreaId ? optional($areas->firstWhere('id', $activeAreaId))->name : 'Semua area';
@endphp
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <title>Rekap Catat Meter - {{ $periodLabel }}</title>
        <style>
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                margin: 32px;
                color: #0f172a;
                background: #fff;
            }

            h1,
            h2 {
                margin: 0 0 12px;
            }

            h1 {
                font-size: 1.5rem;
            }

            h2 {
                font-size: 1.1rem;
                margin-top: 32px;
            }

            p.meta {
                margin: 0 0 4px;
                color: #475569;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 12px;
                font-size: 0.9rem;
            }

            table thead th {
                text-align: left;
                font-size: 0.78rem;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                color: #64748b;
                border-bottom: 2px solid #e2e8f0;
                padding-bottom: 6px;
            }

            table tbody td {
                border-bottom: 1px solid #f1f5f9;
                padding: 6px 0;
            }

            .summary-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                gap: 12px;
                margin-top: 16px;
            }

            .summary-card {
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 12px;
                background: #f8fafc;
            }

            .summary-card span {
                display: block;
                font-size: 0.78rem;
                color: #64748b;
                text-transform: uppercase;
                letter-spacing: 0.08em;
            }

            .summary-card strong {
                display: block;
                margin-top: 6px;
                font-size: 1.15rem;
            }

            .small-note {
                margin-top: 24px;
                font-size: 0.8rem;
                color: #94a3b8;
            }
        </style>
    </head>
    <body>
        <h1>Rekap Catat Meter {{ $periodLabel }}</h1>
        <p class="meta">Area: {{ $activeArea }}</p>
        <p class="meta">Dibuat pada {{ $exportTime }} oleh {{ auth()->user()->name }}</p>

        <div class="summary-grid">
            <div class="summary-card">
                <span>Target</span>
                <strong>{{ $summary['target'] }} pelanggan</strong>
            </div>
            <div class="summary-card">
                <span>Realisasi</span>
                <strong>{{ $summary['completed'] }} selesai</strong>
            </div>
            <div class="summary-card">
                <span>Pending</span>
                <strong>{{ $summary['pending'] }}</strong>
            </div>
            <div class="summary-card">
                <span>Volume</span>
                <strong>{{ number_format($summary['volume'], 2) }} m³</strong>
            </div>
            <div class="summary-card">
                <span>Tagihan</span>
                <strong>Rp {{ number_format($summary['bill'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <h2>Ringkasan Area</h2>
        <table>
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Target</th>
                    <th>Selesai</th>
                    <th>Pending</th>
                    <th>Volume (m³)</th>
                    <th>Tagihan</th>
                    <th>Petugas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($summary['areaSummaries'] as $areaSummary)
                    <tr>
                        <td>{{ $areaSummary['area_name'] }}</td>
                        <td>{{ $areaSummary['target'] }}</td>
                        <td>{{ $areaSummary['completed'] }}</td>
                        <td>{{ $areaSummary['pending'] }}</td>
                        <td>{{ number_format($areaSummary['volume'], 2) }}</td>
                        <td>Rp {{ number_format($areaSummary['bill'], 0, ',', '.') }}</td>
                        <td>{{ implode(', ', $areaSummary['petugas']) ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Tidak ada data area untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <h2>Detail Bacaan</h2>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                    <th>Area</th>
                    <th>Petugas</th>
                    <th>Awal</th>
                    <th>Akhir</th>
                    <th>Volume</th>
                    <th>Tagihan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($readings as $reading)
                    <tr>
                        <td>{{ $reading->customer->customer_code }}</td>
                        <td>{{ $reading->customer->name }}</td>
                        <td>{{ optional($reading->area)->name }}</td>
                        <td>{{ optional($reading->petugas)->name }}</td>
                        <td>{{ $reading->start_reading }}</td>
                        <td>{{ $reading->end_reading }}</td>
                        <td>{{ number_format($reading->usage_m3, 2) }}</td>
                        <td>Rp {{ number_format($reading->bill_amount, 0, ',', '.') }}</td>
                        <td>{{ $reading->recorded_at ? 'Selesai' : 'Belum' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Tidak ada bacaan untuk ditampilkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <p class="small-note">Template ini bersifat siap cetak, gunakan fitur print PDF browser untuk menghasilkan dokumen.</p>
    </body>
</html>

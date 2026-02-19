<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\Customer;
use App\Models\MeterAssignment;
use App\Models\MeterPeriod;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MeterPeriodController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $year = (int) ($request->integer('year') ?? session('catat_meter_year') ?? now()->year);

        if ($year < 2020) {
            $year = now()->year;
        }
        $areaFilter = $this->resolveAreaFilter($request->user());

        if ($areaFilter === []) {
            abort(403, 'Petugas belum memiliki area penugasan.');
        }

        $periodsQuery = MeterPeriod::query()
            ->with(['assignments' => function ($query) use ($areaFilter) {
                if (is_array($areaFilter)) {
                    $query->whereIn('area_id', $areaFilter);
                }
                $query->with('area');
            }])
            ->where('year', $year)
            ->orderByDesc('month');

        $periodsQuery->withCount(['assignments as assignment_count' => function ($query) use ($areaFilter) {
            if (is_array($areaFilter)) {
                $query->whereIn('area_id', $areaFilter);
            }
        }]);

        $periodsQuery->withSum(['assignments as target_customers_sum' => function ($query) use ($areaFilter) {
            if (is_array($areaFilter)) {
                $query->whereIn('area_id', $areaFilter);
            }
        }], 'target_customers');

        $periodsQuery->withCount(['readings as total_readings_count' => function ($query) use ($areaFilter) {
            if (is_array($areaFilter)) {
                $query->whereIn('area_id', $areaFilter);
            }
        }]);

        $periodsQuery->withCount(['readings as recorded_readings_count' => function ($query) use ($areaFilter) {
            if (is_array($areaFilter)) {
                $query->whereIn('area_id', $areaFilter);
            }

            $query->whereNotNull('recorded_at');
        }]);

        $periodsQuery->withSum(['readings as total_usage_m3' => function ($query) use ($areaFilter) {
            if (is_array($areaFilter)) {
                $query->whereIn('area_id', $areaFilter);
            }
        }], 'usage_m3');

        $periodsQuery->withSum(['readings as total_bill_amount' => function ($query) use ($areaFilter) {
            if (is_array($areaFilter)) {
                $query->whereIn('area_id', $areaFilter);
            }
        }], 'bill_amount');

        $periods = $periodsQuery->get()
            ->map(function (MeterPeriod $period) {
                $target = (int) $period->target_customers_sum;
                $completed = (int) $period->recorded_readings_count;
                $pending = max($target - $completed, 0);
                $progress = $target > 0 ? round(($completed / $target) * 100, 1) : 0;

                $period->setAttribute('summary', [
                    'target' => $target,
                    'completed' => $completed,
                    'pending' => $pending,
                    'progress' => $progress,
                    'volume' => (float) $period->total_usage_m3,
                    'bill' => (int) $period->total_bill_amount,
                ]);

                $period->setAttribute('can_delete', $completed === 0);

                return $period;
            });

        $yearOptions = MeterPeriod::select('year')->distinct()->orderByDesc('year')->pluck('year')->all();
        if (! in_array($year, $yearOptions, true)) {
            $yearOptions[] = $year;
        }
        rsort($yearOptions);

        $nextPeriod = $this->suggestNextPeriod();

        session(['catat_meter_year' => $year]);

        return Inertia::render('MeterReading/Index', [
            'year' => $year,
            'yearOptions' => $yearOptions,
            'periods' => $periods,
            'monthOptions' => $this->monthOptions(),
            'nextPeriod' => $nextPeriod,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $exists = MeterPeriod::where('year', $validated['year'])
            ->where('month', $validated['month'])
            ->exists();

        if ($exists) {
            return Redirect::route('catat-meter.index', ['year' => $validated['year']])
                ->with('status', 'Periode sudah tersedia.');
        }

        DB::transaction(function () use ($validated, $request) {
            $period = MeterPeriod::create([
                'year' => $validated['year'],
                'month' => $validated['month'],
                'status' => 'open',
                'notes' => $validated['notes'] ?? null,
                'opened_by' => $request->user()->id,
                'opened_at' => Carbon::now(),
            ]);

            $areas = Area::withCount('customers')->get();

            foreach ($areas as $area) {
                $assignment = MeterAssignment::create([
                    'meter_period_id' => $period->id,
                    'area_id' => $area->id,
                    'target_customers' => $area->customers_count,
                    'status' => $area->customers_count > 0 ? 'pending' : 'idle',
                ]);

                if ($area->customers_count === 0) {
                    continue;
                }

                $customers = Customer::where('area_id', $area->id)->get();
                foreach ($customers as $customer) {
                    MeterReading::create([
                        'meter_period_id' => $period->id,
                        'meter_assignment_id' => $assignment->id,
                        'customer_id' => $customer->id,
                        'area_id' => $area->id,
                        'status' => 'pending',
                    ]);
                }
            }

            ChangeLog::record($request->user(), 'meter-period.open', 'Membuka periode catat meter.', [
                'subject_type' => MeterPeriod::class,
                'subject_id' => $period->id,
                'after' => $period->only(['id', 'year', 'month', 'status', 'opened_by', 'opened_at']),
            ]);
        });

        return Redirect::route('catat-meter.index', ['year' => $validated['year']])
            ->with('status', 'Periode catat meter dibuka.');
    }

    public function destroy(Request $request, MeterPeriod $meterPeriod): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);

        $hasRecorded = $meterPeriod->readings()->whereNotNull('recorded_at')->exists();
        if ($hasRecorded) {
            return Redirect::route('catat-meter.index', ['year' => $meterPeriod->year])
                ->with('error', 'Periode tidak dapat dihapus karena sudah ada pencatatan meter.');
        }

        DB::transaction(function () use ($meterPeriod, $request) {
            $meterPeriod->readings()->delete();
            $meterPeriod->assignments()->delete();
            $before = $meterPeriod->only(['id', 'year', 'month', 'status']);
            $meterPeriod->delete();

            ChangeLog::record($request->user(), 'meter-period.delete', 'Menghapus periode catat meter.', [
                'subject_type' => MeterPeriod::class,
                'subject_id' => $before['id'],
                'before' => $before,
            ]);
        });

        return Redirect::route('catat-meter.index', ['year' => $meterPeriod->year])
            ->with('status', 'Periode catat meter dihapus.');
    }

    public function show(Request $request, MeterPeriod $meterPeriod): InertiaResponse
    {
        return Inertia::render('MeterReading/Show', $this->preparePeriodData($request, $meterPeriod));
    }

    public function pending(Request $request, MeterPeriod $meterPeriod): InertiaResponse
    {
        return Inertia::render('MeterReading/Pending', $this->preparePeriodData($request, $meterPeriod));
    }

    public function inputReading(Request $request, MeterPeriod $meterPeriod, MeterReading $meterReading): InertiaResponse
    {
        $user = $request->user();
        $this->authorizeReadingAccess($user, $meterReading);

        abort_if($meterReading->meter_period_id !== $meterPeriod->id, 404);

        $meterReading->load('customer.area', 'customer.golongan.tariffLevels', 'customer.golongan.nonAirFees', 'period', 'petugas');

        // Get previous reading for this customer
        $previousReading = MeterReading::where('customer_id', $meterReading->customer_id)
            ->where('id', '!=', $meterReading->id)
            ->whereNotNull('recorded_at')
            ->orderByDesc('meter_period_id')
            ->first();

        return Inertia::render('MeterReading/Input', [
            'period' => $meterPeriod,
            'reading' => $meterReading,
            'previousReading' => $previousReading,
        ]);
    }

    private function authorizeReadingAccess(User $user, MeterReading $reading): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $allowedAreas = $user->assignedAreas()->pluck('areas.id')->all();
        if (empty($allowedAreas) && $user->area_id) {
            $allowedAreas = [$user->area_id];
        }

        abort_if(empty($allowedAreas) || !in_array($reading->area_id, $allowedAreas, true), 403, 'Tidak memiliki akses ke pelanggan ini.');
    }

    public function exportExcel(Request $request, MeterPeriod $meterPeriod): Response
    {
        $data = $this->preparePeriodData($request, $meterPeriod);
        $fileName = sprintf(
            'catat-meter-%s-%s.csv',
            $meterPeriod->year,
            str_pad((string) $meterPeriod->month, 2, '0', STR_PAD_LEFT)
        );

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Kode Pelanggan',
                'Nama',
                'Area',
                'Petugas',
                'Angka Awal',
                'Angka Akhir',
                'Volume (m3)',
                'Tagihan',
                'Status',
                'Dicatat Pada',
            ]);

            $data['readings']->each(function (MeterReading $reading) use ($handle) {
                fputcsv($handle, [
                    $reading->customer->customer_code,
                    $reading->customer->name,
                    optional($reading->area)->name,
                    optional($reading->petugas)->name,
                    $reading->start_reading,
                    $reading->end_reading,
                    $reading->usage_m3,
                    $reading->bill_amount,
                    $reading->recorded_at ? 'Selesai' : 'Belum',
                    optional($reading->recorded_at)->toDateTimeString(),
                ]);
            });

            fclose($handle);
        }, $fileName, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request, MeterPeriod $meterPeriod): Response
    {
        $data = $this->preparePeriodData($request, $meterPeriod);

        return response()->view('exports.catat-meter-summary', $data);
    }

    protected function resolveAreaFilter(?User $user): ?array
    {
        if (! $user || $user->isAdmin()) {
            return null;
        }

        $areaIds = $user->assignedAreas()->pluck('areas.id')->filter()->all();
        if (! empty($areaIds)) {
            return $areaIds;
        }

        if ($user->area_id) {
            return [$user->area_id];
        }

        return [];
    }

    /**
     * @param  Collection<int, MeterAssignment>  $assignments
     * @param  Collection<int, MeterReading>  $readings
     */
    protected function preparePeriodData(Request $request, MeterPeriod $meterPeriod): array
    {
        $areaFilter = $this->resolveAreaFilter($request->user());
        if ($areaFilter === []) {
            abort(403, 'Petugas belum memiliki area penugasan.');
        }

        $meterPeriod->load([
            'assignments.area',
            'readings.customer.area',
            'readings.customer.golongan',
            'readings.petugas',
            'readings.bill',
            'readings.billPublishedBy',
        ]);

        if (is_array($areaFilter)) {
            $meterPeriod->setRelation('assignments', $meterPeriod->assignments->whereIn('area_id', $areaFilter));
            $meterPeriod->setRelation('readings', $meterPeriod->readings->whereIn('area_id', $areaFilter));
        }

        $areas = $meterPeriod->assignments->pluck('area')->filter()->unique('id')->values();
        $activeAreaId = $request->integer('area');
        if ($activeAreaId && $areas->where('id', $activeAreaId)->isEmpty()) {
            $activeAreaId = null;
        }

        $readings = $meterPeriod->readings;
        if ($activeAreaId) {
            $readings = $readings->where('area_id', $activeAreaId);
        }

        $readings = $readings->sortBy(function (MeterReading $reading) {
            return sprintf('%04d-%s', $reading->area_id, $reading->customer->customer_code ?? '');
        })->values();

        $completedReadings = $readings->filter(fn (MeterReading $reading) => ! is_null($reading->recorded_at))->values();
        $pendingReadings = $readings->filter(fn (MeterReading $reading) => is_null($reading->recorded_at))->values();
        $summary = $this->buildSummary($meterPeriod->assignments, $readings);

        return [
            'period' => $meterPeriod,
            'areas' => $areas,
            'activeAreaId' => $activeAreaId,
            'readings' => $readings,
            'completedReadings' => $completedReadings,
            'summary' => $summary,
            'pendingReadings' => $pendingReadings,
        ];
    }

    /**
     * @param  Collection<int, MeterAssignment>  $assignments
     * @param  Collection<int, MeterReading>  $readings
     */
    protected function buildSummary(Collection $assignments, Collection $readings): array
    {
        $target = (int) $assignments->sum('target_customers');
        if ($target === 0) {
            $target = max($readings->count(), 0);
        }
        $completed = $readings->filter(fn (MeterReading $reading) => ! is_null($reading->recorded_at))->count();
        $pending = max($target - $completed, 0);
        $progress = $target > 0 ? round(($completed / $target) * 100, 1) : 0;
        $volume = (float) $readings->sum('usage_m3');
        $bill = (int) $readings->sum('bill_amount');

        $areaSummaries = $assignments->map(function (MeterAssignment $assignment) use ($readings) {
            $areaReadings = $readings->where('area_id', $assignment->area_id);
            $areaTarget = (int) $assignment->target_customers;
            $areaCompleted = $areaReadings->whereNotNull('recorded_at')->count();
            $areaPending = max($areaTarget - $areaCompleted, 0);

            return [
                'area_id' => $assignment->area_id,
                'area_name' => optional($assignment->area)->name ?? 'Area Tanpa Nama',
                'target' => $areaTarget,
                'completed' => $areaCompleted,
                'pending' => $areaPending,
                'volume' => (float) $areaReadings->sum('usage_m3'),
                'bill' => (int) $areaReadings->sum('bill_amount'),
                'petugas' => $areaReadings->pluck('petugas.name')->filter()->unique()->values()->all(),
            ];
        })->sortBy('area_name')->values();

        return compact('target', 'completed', 'pending', 'progress', 'volume', 'bill', 'areaSummaries');
    }

    protected function suggestNextPeriod(): array
    {
        $latest = MeterPeriod::orderByDesc('year')->orderByDesc('month')->first();
        if (! $latest) {
            $now = Carbon::now();
            return ['year' => $now->year, 'month' => $now->month];
        }

        $next = Carbon::create($latest->year, $latest->month, 1)->addMonth();

        return ['year' => $next->year, 'month' => $next->month];
    }

    protected function monthOptions(): array
    {
        $options = [];
        for ($month = 1; $month <= 12; $month++) {
            $options[$month] = Carbon::create(null, $month, 1)->translatedFormat('F');
        }

        return $options;
    }
}

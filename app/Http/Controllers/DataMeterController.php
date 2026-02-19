<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Bill;
use App\Models\MeterPeriod;
use App\Models\MeterReading;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DataMeterController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403, 'Hanya admin yang dapat mengakses menu ini.');

        $year = (int) ($request->integer('year') ?? now()->year);

        $periods = MeterPeriod::query()
            ->where('year', $year)
            ->withCount(['readings as total_readings'])
            ->withCount(['readings as recorded_readings' => function ($query) {
                $query->whereNotNull('recorded_at');
            }])
            ->withCount(['readings as published_readings' => function ($query) {
                $query->whereNotNull('bill_published_at');
            }])
            ->withCount(['readings as paid_readings' => function ($query) {
                $query->whereHas('bill', function ($q) {
                    $q->where('status', Bill::STATUS_PAID);
                });
            }])
            ->withSum('readings as total_bill_amount', 'bill_amount')
            ->orderByDesc('month')
            ->get()
            ->map(function (MeterPeriod $period) {
                $period->setAttribute('summary', [
                    'total' => $period->total_readings,
                    'recorded' => $period->recorded_readings,
                    'published' => $period->published_readings,
                    'paid' => $period->paid_readings,
                    'total_bill' => (int) $period->total_bill_amount,
                ]);
                return $period;
            });

        $yearOptions = MeterPeriod::select('year')->distinct()->orderByDesc('year')->pluck('year')->all();
        if (! in_array($year, $yearOptions, true)) {
            $yearOptions[] = $year;
        }
        rsort($yearOptions);

        return Inertia::render('DataMeter/Index', [
            'year' => $year,
            'yearOptions' => $yearOptions,
            'periods' => $periods,
        ]);
    }

    public function show(Request $request, MeterPeriod $meterPeriod): InertiaResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403, 'Hanya admin yang dapat mengakses menu ini.');

        $search = $request->get('search', '');
        $status = $request->get('status', 'all'); // all | recorded | published | paid | unpaid
        $areaId = $request->integer('area');

        $meterPeriod->load([
            'assignments.area',
            'readings.customer.area',
            'readings.customer.golongan',
            'readings.petugas',
            'readings.bill.payments',
            'readings.billPublishedBy',
        ]);

        $areas = $meterPeriod->assignments->pluck('area')->filter()->unique('id')->values();

        $readings = $meterPeriod->readings;

        // Filter by area
        if ($areaId) {
            $readings = $readings->where('area_id', $areaId);
        }

        // Filter by status
        if ($status === 'recorded') {
            $readings = $readings->whereNotNull('recorded_at')->whereNull('bill_published_at');
        } elseif ($status === 'published') {
            $readings = $readings->whereNotNull('bill_published_at')->filter(function ($r) {
                return !$r->bill || $r->bill->status !== Bill::STATUS_PAID;
            });
        } elseif ($status === 'paid') {
            $readings = $readings->filter(function ($r) {
                return $r->bill && $r->bill->status === Bill::STATUS_PAID;
            });
        } elseif ($status === 'unpaid') {
            $readings = $readings->whereNotNull('bill_published_at')->filter(function ($r) {
                return !$r->bill || in_array($r->bill->status, [Bill::STATUS_PUBLISHED, Bill::STATUS_PARTIAL]);
            });
        }

        // Filter by search
        if ($search) {
            $searchLower = strtolower($search);
            $readings = $readings->filter(function ($r) use ($searchLower) {
                return str_contains(strtolower($r->customer->name ?? ''), $searchLower)
                    || str_contains(strtolower($r->customer->customer_code ?? ''), $searchLower);
            });
        }

        $readings = $readings->sortBy(function (MeterReading $reading) {
            return sprintf('%04d-%s', $reading->area_id, $reading->customer->customer_code ?? '');
        })->values();

        $summary = [
            'total' => $meterPeriod->readings->count(),
            'recorded' => $meterPeriod->readings->whereNotNull('recorded_at')->count(),
            'published' => $meterPeriod->readings->whereNotNull('bill_published_at')->count(),
            'paid' => $meterPeriod->readings->filter(fn($r) => $r->bill && $r->bill->status === Bill::STATUS_PAID)->count(),
            'total_bill' => (int) $meterPeriod->readings->sum('bill_amount'),
        ];

        return Inertia::render('DataMeter/Show', [
            'period' => $meterPeriod,
            'areas' => $areas,
            'readings' => $readings,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'area' => $areaId,
            ],
        ]);
    }
}

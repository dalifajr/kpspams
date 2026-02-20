<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MeterPeriod;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeterPeriodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $areaIds = $this->resolveAreaIds($user);

        if ($areaIds === []) {
            return response()->json([
                'message' => 'Akun petugas belum memiliki area penugasan.',
            ], 403);
        }

        $perPage = min(max($request->integer('per_page', 12), 1), 50);

        $periods = MeterPeriod::query()
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($perPage)
            ->through(function (MeterPeriod $period) use ($areaIds) {
                $summary = $this->buildSummary($period, $areaIds);

                return [
                    'id' => $period->id,
                    'year' => $period->year,
                    'month' => $period->month,
                    'status' => $period->status,
                    'label' => sprintf('%02d/%d', $period->month, $period->year),
                    'opened_at' => optional($period->opened_at)?->toIso8601String(),
                    'summary' => $summary,
                ];
            });

        return response()->json($periods);
    }

    public function show(Request $request, MeterPeriod $meterPeriod): JsonResponse
    {
        $user = $request->user();
        $areaIds = $this->resolveAreaIds($user);

        if ($areaIds === []) {
            return response()->json([
                'message' => 'Akun petugas belum memiliki area penugasan.',
            ], 403);
        }

        return response()->json([
            'data' => [
                'id' => $meterPeriod->id,
                'year' => $meterPeriod->year,
                'month' => $meterPeriod->month,
                'status' => $meterPeriod->status,
                'label' => sprintf('%02d/%d', $meterPeriod->month, $meterPeriod->year),
                'opened_at' => optional($meterPeriod->opened_at)?->toIso8601String(),
                'summary' => $this->buildSummary($meterPeriod, $areaIds),
            ],
        ]);
    }

    public function active(Request $request): JsonResponse
    {
        $user = $request->user();
        $areaIds = $this->resolveAreaIds($user);

        if ($areaIds === []) {
            return response()->json([
                'message' => 'Akun petugas belum memiliki area penugasan.',
            ], 403);
        }

        $period = MeterPeriod::query()
            ->where('status', 'open')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (! $period) {
            $period = MeterPeriod::query()
                ->orderByDesc('year')
                ->orderByDesc('month')
                ->first();
        }

        if (! $period) {
            return response()->json([
                'message' => 'Belum ada periode catat meter.',
            ], 404);
        }

        $readingsQuery = MeterReading::query()->where('meter_period_id', $period->id);
        if (is_array($areaIds)) {
            $readingsQuery->whereIn('area_id', $areaIds);
        }

        $total = (clone $readingsQuery)->count();
        $recorded = (clone $readingsQuery)->whereNotNull('recorded_at')->count();

        return response()->json([
            'data' => [
                'id' => $period->id,
                'year' => $period->year,
                'month' => $period->month,
                'status' => $period->status,
                'label' => sprintf('%02d/%d', $period->month, $period->year),
                'opened_at' => optional($period->opened_at)?->toIso8601String(),
                'summary' => [
                    'total' => $total,
                    'recorded' => $recorded,
                    'pending' => max($total - $recorded, 0),
                ],
            ],
        ]);
    }

    protected function resolveAreaIds(User $user): ?array
    {
        if ($user->isAdmin()) {
            return null;
        }

        $areaIds = $user->assignedAreas()->pluck('areas.id')->filter()->values()->all();
        if (! empty($areaIds)) {
            return $areaIds;
        }

        if ($user->area_id) {
            return [$user->area_id];
        }

        return [];
    }

    protected function buildSummary(MeterPeriod $period, ?array $areaIds): array
    {
        $readingsBaseQuery = MeterReading::query()->where('meter_period_id', $period->id);

        if (is_array($areaIds)) {
            $readingsBaseQuery->whereIn('area_id', $areaIds);
        }

        $total = (clone $readingsBaseQuery)->count();
        $recorded = (clone $readingsBaseQuery)->whereNotNull('recorded_at')->count();
        $published = (clone $readingsBaseQuery)->whereNotNull('bill_published_at')->count();
        $paid = (clone $readingsBaseQuery)
            ->whereHas('bill', function (Builder $query): void {
                $query->where('status', 'paid');
            })
            ->count();

        return [
            'total' => $total,
            'recorded' => $recorded,
            'pending' => max($total - $recorded, 0),
            'published' => $published,
            'paid' => $paid,
        ];
    }
}

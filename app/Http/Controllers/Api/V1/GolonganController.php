<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Golongan;
use Illuminate\Http\JsonResponse;

class GolonganController extends Controller
{
    public function index(): JsonResponse
    {
        $golongans = Golongan::query()
            ->withCount(['customers', 'tariffLevels', 'nonAirFees'])
            ->orderBy('code')
            ->get()
            ->map(function (Golongan $golongan): array {
                return [
                    'id' => $golongan->id,
                    'code' => $golongan->code,
                    'name' => $golongan->name,
                    'customers_count' => $golongan->customers_count,
                    'tariffs_count' => $golongan->tariff_levels_count,
                    'fees_count' => $golongan->non_air_fees_count,
                ];
            })
            ->values();

        return response()->json([
            'data' => $golongans,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MeterPeriod;
use App\Models\MeterReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class MeterReadingController extends Controller
{
    /**
     * Display a listing of meter readings for a specific period.
     */
    public function index(Request $request, MeterPeriod $meterPeriod): JsonResponse
    {
        $user = $request->user();

        $query = MeterReading::query()
            ->with(['customer', 'area'])
            ->where('meter_period_id', $meterPeriod->id);

        if (!$user->isAdmin()) {
            $allowedAreas = collect($user->assignedAreas()->pluck('areas.id'));
            if ($allowedAreas->isEmpty() && $user->area_id) {
                $allowedAreas = collect([$user->area_id]);
            }

            if ($allowedAreas->isEmpty()) {
                return response()->json([]);
            }

            $query->whereIn('area_id', $allowedAreas);
        }

        if ($request->search) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('customer_code', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by unrecorded, recorded, etc
        if ($request->status === 'unrecorded') {
            $query->whereNull('recorded_at');
        } elseif ($request->status === 'recorded') {
            $query->whereNotNull('recorded_at');
        }

        $readings = $query->paginate($request->integer('per_page', 15));

        return response()->json($readings);
    }

    /**
     * Update the specified meter reading.
     */
    public function update(Request $request, MeterPeriod $meterPeriod, MeterReading $meterReading): JsonResponse
    {
        if ($meterReading->meter_period_id !== $meterPeriod->id) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        $user = $request->user();

        if (!$user->isAdmin()) {
            $allowedAreas = collect($user->assignedAreas()->pluck('areas.id'));
            if ($allowedAreas->isEmpty() && $user->area_id) {
                $allowedAreas = collect([$user->area_id]);
            }

            if ($allowedAreas->isEmpty() || !$allowedAreas->contains($meterReading->area_id)) {
                return response()->json(['message' => 'Tidak boleh memperbarui pelanggan di area lain.'], 403);
            }
        }

        $data = $request->validate([
            'start_reading' => ['nullable', 'numeric', 'min:0'],
            'end_reading' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $startReading = $meterReading->start_reading ?? 0;
        $endReading = (float) $data['end_reading'];

        if ($endReading < $startReading) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['end_reading' => ['Stand akhir tidak boleh lebih kecil dari stand awal.']],
            ], 422);
        }

        $usage = max($endReading - $startReading, 0);

        $meterReading->fill([
            'start_reading' => $startReading,
            'end_reading' => $endReading,
            'usage_m3' => $usage,
            'note' => $data['notes'] ?? null,
            'petugas_id' => $user->id,
            'status' => 'unpaid',
            'recorded_at' => Carbon::now(),
        ]);

        if ($request->hasFile('photo')) {
             $meterReading->photo_path = $this->storeCompressedPhoto($request->file('photo'));
        }

        $meterReading->loadMissing('customer.golongan.tariffLevels', 'customer.golongan.nonAirFees');
        $meterReading->bill_amount = $this->calculateBillAmount($meterReading, $usage);

        $meterReading->save();

        return response()->json([
            'message' => 'Meter berhasil dicatat',
            'data' => $meterReading
        ]);
    }

    protected function calculateBillAmount(MeterReading $meterReading, float $usage): int
    {
        $golongan = optional($meterReading->customer)->golongan;
        if (! $golongan) {
            return (int) round($usage * 1000);
        }

        $total = 0;
        $remaining = $usage;
        $tariffs = $golongan->tariffLevels ?? collect();

        foreach ($tariffs as $tariff) {
            if ($remaining <= 0) {
                break;
            }

            $cap = $tariff->meter_end !== null
                ? max(($tariff->meter_end - $tariff->meter_start) + 1, 0)
                : null;

            $portion = $cap ? min($remaining, $cap) : $remaining;
            $total += $portion * (float) $tariff->price;
            $remaining -= $portion;
        }

        if ($remaining > 0 && $tariffs->isNotEmpty()) {
            $lastTariff = $tariffs->last();
            $total += $remaining * (float) $lastTariff->price;
        }

        foreach ($golongan->nonAirFees ?? collect() as $fee) {
            $total += (float) $fee->price;
        }

        return (int) round($total);
    }
    
    protected function storeCompressedPhoto(UploadedFile $file): string
    {
        return $file->store('meter-readings', 'public');
    }
}

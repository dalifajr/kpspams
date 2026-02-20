<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Publish bill for a recorded meter reading
     */
    public function publish(Request $request, MeterReading $meterReading): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            $allowedAreas = $user->assignedAreas()->pluck('areas.id')->all();
            if (empty($allowedAreas) && $user->area_id) {
                $allowedAreas = [$user->area_id];
            }
            if (!in_array($meterReading->area_id, $allowedAreas, true)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        if (!$meterReading->recorded_at) {
            return response()->json(['message' => 'Meter belum dicatat.'], 400);
        }

        if ($meterReading->bill_published_at) {
            return response()->json(['message' => 'Tagihan sudah diterbitkan.'], 400);
        }

        DB::transaction(function () use ($meterReading, $user) {
            $meterReading->update([
                'bill_published_at' => Carbon::now(),
                'bill_published_by' => $user->id,
                'status' => 'unpaid',
            ]);

            $meterReading->loadMissing('period');

            Bill::create([
                'meter_reading_id' => $meterReading->id,
                'customer_id' => $meterReading->customer_id,
                'meter_period_id' => $meterReading->meter_period_id,
                'year' => $meterReading->period->year,
                'month' => $meterReading->period->month,
                'water_usage_amount' => (int) $meterReading->bill_amount,
                'admin_fee' => 0,
                'other_fees' => 0,
                'total_amount' => (int) $meterReading->bill_amount,
                'paid_amount' => 0,
                'status' => Bill::STATUS_PUBLISHED,
                'published_by' => $user->id,
                'published_at' => Carbon::now(),
            ]);
        });

        return response()->json([
            'message' => 'Tagihan berhasil diterbitkan.',
            'data' => $meterReading->fresh()->load('bill'),
        ]);
    }

    /**
     * Unpublish bill (admin only and unpaid)
     */
    public function unpublish(Request $request, MeterReading $meterReading): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Hanya admin yang dapat membatalkan tagihan.'], 403);
        }

        if (!$meterReading->bill_published_at) {
            return response()->json(['message' => 'Tagihan belum diterbitkan.'], 400);
        }

        $bill = $meterReading->bill;
        if ($bill && $bill->paid_amount > 0) {
            return response()->json(['message' => 'Tidak dapat membatalkan tagihan yang sudah dibayar.'], 400);
        }

        DB::transaction(function () use ($meterReading) {
            $meterReading->bill?->delete();

            $meterReading->update([
                'bill_published_at' => null,
                'bill_published_by' => null,
                'status' => 'recorded',
            ]);
        });

        return response()->json([
            'message' => 'Tagihan berhasil dibatalkan.',
            'data' => $meterReading->fresh()->load('bill'),
        ]);
    }

    /**
     * Get active bills for a customer
     */
    public function customerBills(Request $request, Customer $customer): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin()) {
            $allowedAreas = $user->assignedAreas()->pluck('areas.id')->all();
            if (empty($allowedAreas) && $user->area_id) {
                $allowedAreas = [$user->area_id];
            }
            if (!in_array($customer->area_id, $allowedAreas, true)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $bills = Bill::where('customer_id', $customer->id)
            ->whereIn('status', [Bill::STATUS_PUBLISHED, Bill::STATUS_PARTIAL])
            ->with(['period', 'meterReading'])
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->map(function (Bill $bill) {
                return [
                    'id' => $bill->id,
                    'period_label' => $bill->month . '/' . $bill->year,
                    'total_amount' => $bill->total_amount,
                    'paid_amount' => $bill->paid_amount,
                    'remaining' => $bill->remainingAmount(),
                    'usage_m3' => $bill->meterReading?->usage_m3,
                    'status' => $bill->status,
                ];
            });

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'customer_code' => $customer->customer_code,
            ],
            'bills' => $bills,
        ]);
    }

    /**
     * Process payment for a bill
     */
    public function pay(Request $request, Bill $bill): JsonResponse
    {
        $user = $request->user();
        $bill->loadMissing('customer');
        
        if (!$user->isAdmin()) {
            $allowedAreas = $user->assignedAreas()->pluck('areas.id')->all();
            if (empty($allowedAreas) && $user->area_id) {
                $allowedAreas = [$user->area_id];
            }
            if (!in_array($bill->customer->area_id, $allowedAreas, true)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        if (!$bill->isPublished()) {
            return response()->json(['message' => 'Tagihan belum diterbitkan.'], 400);
        }

        if ($bill->isFullyPaid()) {
            return response()->json(['message' => 'Tagihan sudah lunas.'], 400);
        }

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'in:cash,transfer,qris'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($bill, $validated, $user) {
            Payment::create([
                'bill_id' => $bill->id,
                'customer_id' => $bill->customer_id,
                'collected_by' => $user->id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'paid_at' => Carbon::now(),
            ]);

            $bill->updatePaymentStatus();
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dicatat.',
            'bill' => $bill->fresh()
        ]);
    }
}

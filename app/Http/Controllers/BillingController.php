<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\ChangeLog;
use App\Models\Customer;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
    /**
     * Publish bill for a meter reading
     */
    public function publish(Request $request, MeterReading $meterReading): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccessToReading($user, $meterReading);

        if (!$meterReading->recorded_at) {
            return back()->with('error', 'Meter belum dicatat.');
        }

        if ($meterReading->bill_published_at) {
            return back()->with('error', 'Tagihan sudah diterbitkan.');
        }

        DB::transaction(function () use ($meterReading, $user) {
            $meterReading->update([
                'bill_published_at' => Carbon::now(),
                'bill_published_by' => $user->id,
                'status' => 'unpaid',
            ]);

            // Create bill record
            $meterReading->loadMissing('customer.golongan.nonAirFees', 'period');

            $waterAmount = (int) $meterReading->bill_amount;
            $adminFee = 0;
            $otherFees = 0;

            foreach ($meterReading->customer->golongan?->nonAirFees ?? [] as $fee) {
                $otherFees += (int) $fee->price;
            }

            // Actually the bill_amount already includes non-air fees from MeterReadingController
            // So we just use bill_amount as total
            Bill::create([
                'meter_reading_id' => $meterReading->id,
                'customer_id' => $meterReading->customer_id,
                'meter_period_id' => $meterReading->meter_period_id,
                'year' => $meterReading->period->year,
                'month' => $meterReading->period->month,
                'water_usage_amount' => $waterAmount,
                'admin_fee' => $adminFee,
                'other_fees' => 0, // Already included in water_usage_amount
                'total_amount' => $waterAmount,
                'paid_amount' => 0,
                'status' => Bill::STATUS_PUBLISHED,
                'published_by' => $user->id,
                'published_at' => Carbon::now(),
            ]);

            ChangeLog::record($user, 'bill.publish', 'Menerbitkan tagihan pelanggan.', [
                'subject_type' => MeterReading::class,
                'subject_id' => $meterReading->id,
                'after' => [
                    'customer_id' => $meterReading->customer_id,
                    'bill_amount' => $waterAmount,
                ],
            ]);
        });

        return back()->with('status', 'Tagihan berhasil diterbitkan.');
    }

    /**
     * Unpublish bill (admin only)
     */
    public function unpublish(Request $request, MeterReading $meterReading): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isAdmin(), 403);

        if (!$meterReading->bill_published_at) {
            return back()->with('error', 'Tagihan belum diterbitkan.');
        }

        // Check if there are payments
        $bill = $meterReading->bill;
        if ($bill && $bill->paid_amount > 0) {
            return back()->with('error', 'Tidak dapat membatalkan tagihan yang sudah dibayar.');
        }

        DB::transaction(function () use ($meterReading, $user) {
            $meterReading->bill?->delete();

            $meterReading->update([
                'bill_published_at' => null,
                'bill_published_by' => null,
                'status' => 'recorded',
            ]);

            ChangeLog::record($user, 'bill.unpublish', 'Membatalkan penerbitan tagihan.', [
                'subject_type' => MeterReading::class,
                'subject_id' => $meterReading->id,
            ]);
        });

        return back()->with('status', 'Tagihan berhasil dibatalkan.');
    }

    /**
     * Get active bills for a customer (for payment modal)
     */
    public function customerBills(Request $request, Customer $customer): Response
    {
        $user = $request->user();
        $this->authorizeAccessToCustomer($user, $customer);

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

        return Inertia::render('Billing/CustomerBills', [
            'customer' => $customer->load('area', 'golongan'),
            'bills' => $bills,
        ]);
    }

    /**
     * Get active bills for a customer as JSON (for AJAX payment modal)
     */
    public function getCustomerBillsJson(Request $request, Customer $customer): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $this->authorizeAccessToCustomer($user, $customer);

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
    public function pay(Request $request, Bill $bill): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeAccessToCustomer($user, $bill->customer);

        if (!$bill->isPublished()) {
            return back()->with('error', 'Tagihan belum diterbitkan.');
        }

        if ($bill->isFullyPaid()) {
            return back()->with('error', 'Tagihan sudah lunas.');
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

            ChangeLog::record($user, 'payment.create', 'Menerima pembayaran tagihan.', [
                'subject_type' => Bill::class,
                'subject_id' => $bill->id,
                'after' => [
                    'customer_id' => $bill->customer_id,
                    'amount' => $validated['amount'],
                    'method' => $validated['method'],
                ],
            ]);
        });

        return back()->with('status', 'Pembayaran berhasil dicatat.');
    }

    private function authorizeAccessToReading(User $user, MeterReading $reading): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $allowedAreas = $user->assignedAreas()->pluck('areas.id')->all();
        if (empty($allowedAreas) && $user->area_id) {
            $allowedAreas = [$user->area_id];
        }

        abort_if(!in_array($reading->area_id, $allowedAreas, true), 403);
    }

    private function authorizeAccessToCustomer(User $user, Customer $customer): void
    {
        if ($user->isAdmin()) {
            return;
        }

        $allowedAreas = $user->assignedAreas()->pluck('areas.id')->all();
        if (empty($allowedAreas) && $user->area_id) {
            $allowedAreas = [$user->area_id];
        }

        abort_if(!in_array($customer->area_id, $allowedAreas, true), 403);
    }
}

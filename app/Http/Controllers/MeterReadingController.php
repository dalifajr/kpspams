<?php

namespace App\Http\Controllers;

use App\Models\MeterPeriod;
use App\Models\MeterReading;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MeterReadingController extends Controller
{
    public function update(Request $request, MeterPeriod $meterPeriod, MeterReading $meterReading): RedirectResponse
    {
        abort_if($meterReading->meter_period_id !== $meterPeriod->id, 404);

        $user = $request->user();

        if (! $user->isAdmin()) {
            $allowedAreas = collect($user->assignedAreas()->pluck('areas.id'));
            if ($allowedAreas->isEmpty() && $user->area_id) {
                $allowedAreas = collect([$user->area_id]);
            }

            if ($allowedAreas->isEmpty() || ! $allowedAreas->contains($meterReading->area_id)) {
                abort(403, 'Tidak boleh memperbarui pelanggan di area lain.');
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

        // Validate end_reading is greater than or equal to start_reading
        if ($endReading < $startReading) {
            return Redirect::back()->withInput()->withErrors([
                'end_reading' => 'Stand akhir tidak boleh lebih kecil dari stand awal.',
            ]);
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

        // Calculate bill amount
        $meterReading->loadMissing('customer.golongan.tariffLevels', 'customer.golongan.nonAirFees');
        $meterReading->bill_amount = $this->calculateBillAmount($meterReading, $usage);

        $meterReading->save();

        return Redirect::route('catat-meter.show', ['meterPeriod' => $meterPeriod])
            ->with('status', 'Meter berhasil dicatat: ' . $meterReading->customer->name . ' - ' . $usage . ' m³');
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
        try {
            $contents = file_get_contents($file->getRealPath());
            $image = imagecreatefromstring($contents);
            if (! $image) {
                return $file->store('meter-readings', 'public');
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $maxWidth = 1280;
            if ($width > $maxWidth) {
                $ratio = $maxWidth / $width;
                $newWidth = (int) round($width * $ratio);
                $newHeight = (int) round($height * $ratio);
            } else {
                $newWidth = $width;
                $newHeight = $height;
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            $background = imagecolorallocate($resized, 255, 255, 255);
            imagefill($resized, 0, 0, $background);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

            ob_start();
            imagejpeg($resized, null, 78);
            $jpegData = ob_get_clean();
            imagedestroy($image);
            imagedestroy($resized);

            if (! $jpegData) {
                return $file->store('meter-readings', 'public');
            }

            $path = 'meter-readings/' . Str::uuid() . '.jpg';
            Storage::disk('public')->put($path, $jpegData);

            return $path;
        } catch (\Throwable $e) {
            return $file->store('meter-readings', 'public');
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGolonganRequest;
use App\Http\Requests\UpdateGolonganRequest;
use App\Models\ChangeLog;
use App\Models\Golongan;
use App\Models\GolonganNonAirFee;
use App\Models\GolonganTariff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GolonganController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        $golongans = Golongan::withCount(['tariffLevels', 'nonAirFees', 'customers'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Golongan/Index', [
            'golongans' => $golongans,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        return Inertia::render('Golongan/Create');
    }

    public function store(StoreGolonganRequest $request): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $data = $request->validated();

        $golongan = DB::transaction(function () use ($data) {
            $golongan = Golongan::create([
                'code' => strtoupper($data['code']),
                'name' => trim($data['name']),
            ]);

            $golongan->tariffLevels()->createMany($this->formatTariffs($data['tariffs']));

            return $golongan;
        });

        ChangeLog::record($request->user(), 'golongan.create', 'Menambahkan golongan baru.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'after' => $golongan->only(['id', 'code', 'name']),
        ]);

        return redirect()->route('menu.golongan.show', $golongan)->with('status', 'Golongan berhasil ditambahkan.');
    }

    public function show(Request $request, Golongan $golongan): Response
    {
        $this->ensureAdmin($request->user());

        $golongan->load([
            'tariffLevels',
            'nonAirFees',
        ])->loadCount(['tariffLevels', 'nonAirFees', 'customers']);

        return Inertia::render('Golongan/Show', [
            'golongan' => $golongan,
        ]);
    }

    public function edit(Request $request, Golongan $golongan): Response
    {
        $this->ensureAdmin($request->user());

        return Inertia::render('Golongan/Edit', [
            'golongan' => $golongan->load('tariffLevels'),
        ]);
    }

    public function update(UpdateGolonganRequest $request, Golongan $golongan): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $data = $request->validated();

        $before = $golongan->only(['code', 'name']);

        DB::transaction(function () use ($golongan, $data) {
            $golongan->update([
                'code' => strtoupper($data['code']),
                'name' => trim($data['name']),
            ]);

            $golongan->tariffLevels()->delete();
            $golongan->tariffLevels()->createMany($this->formatTariffs($data['tariffs']));
        });

        ChangeLog::record($request->user(), 'golongan.update', 'Memperbarui data golongan.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'before' => $before,
            'after' => $golongan->only(['code', 'name']),
            'undo' => [
                'type' => 'update',
                'model' => Golongan::class,
                'id' => $golongan->id,
                'data' => $before,
            ],
        ]);

        return redirect()->route('menu.golongan.show', $golongan)->with('status', 'Golongan berhasil diperbarui.');
    }

    public function destroy(Request $request, Golongan $golongan): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        if ($golongan->customers()->exists()) {
            return redirect()
                ->route('menu.golongan.show', $golongan)
                ->with('error', 'Golongan tidak dapat dihapus karena masih digunakan oleh pelanggan.');
        }

        $before = $golongan->only(['id', 'code', 'name']);
        $golongan->delete();

        ChangeLog::record($request->user(), 'golongan.delete', 'Menghapus golongan.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'before' => $before,
        ]);

        return redirect()->route('menu.golongan.index')->with('status', 'Golongan dihapus.');
    }

    public function storeTariff(Request $request, Golongan $golongan): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $data = $request->validate([
            'meter_start' => ['required', 'numeric', 'min:0'],
            'meter_end' => ['nullable', 'numeric', 'gt:meter_start'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $golongan->tariffLevels()->create([
            'meter_start' => (float) $data['meter_start'],
            'meter_end' => (array_key_exists('meter_end', $data) && $data['meter_end'] !== null)
                ? (float) $data['meter_end']
                : null,
            'price' => (float) $data['price'],
        ]);

        ChangeLog::record($request->user(), 'golongan.add-tariff', 'Menambahkan tarif golongan.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'after' => [
                'meter_start' => (float) $data['meter_start'],
                'meter_end' => (array_key_exists('meter_end', $data) && $data['meter_end'] !== null)
                    ? (float) $data['meter_end']
                    : null,
                'price' => (float) $data['price'],
            ],
        ]);

        return redirect()->route('menu.golongan.show', $golongan)->with('status', 'Tarif berhasil ditambahkan.');
    }

    public function destroyTariff(Request $request, Golongan $golongan, GolonganTariff $tariff): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_unless($tariff->golongan_id === $golongan->id, 404);

        $before = $tariff->only(['id', 'meter_start', 'meter_end', 'price']);
        $tariff->delete();

        ChangeLog::record($request->user(), 'golongan.delete-tariff', 'Menghapus tarif golongan.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'before' => $before,
        ]);

        return redirect()->route('menu.golongan.show', $golongan)->with('status', 'Tarif dihapus.');
    }

    public function storeNonAirFee(Request $request, Golongan $golongan): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $golongan->nonAirFees()->create([
            'label' => $data['label'],
            'price' => (float) $data['price'],
        ]);

        ChangeLog::record($request->user(), 'golongan.add-fee', 'Menambahkan biaya non air.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'after' => [
                'label' => $data['label'],
                'price' => (float) $data['price'],
            ],
        ]);

        return redirect()->route('menu.golongan.show', $golongan)->with('status', 'Biaya non air ditambahkan.');
    }

    public function destroyNonAirFee(Request $request, Golongan $golongan, GolonganNonAirFee $fee): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_unless($fee->golongan_id === $golongan->id, 404);

        $before = $fee->only(['id', 'label', 'price']);
        $fee->delete();

        ChangeLog::record($request->user(), 'golongan.delete-fee', 'Menghapus biaya non air.', [
            'subject_type' => Golongan::class,
            'subject_id' => $golongan->id,
            'before' => $before,
        ]);

        return redirect()->route('menu.golongan.show', $golongan)->with('status', 'Biaya non air dihapus.');
    }

    private function ensureAdmin(?User $user): void
    {
        abort_if(! $user || ! $user->isAdmin(), 403);
    }

    /**
     * @param  array<int, array<string, mixed>>  $tariffs
     * @return array<int, array<string, float|null>>
     */
    private function formatTariffs(array $tariffs): array
    {
        return collect($tariffs)
            ->filter(fn ($tariff) => is_array($tariff))
            ->map(function (array $tariff): array {
                $meterEnd = $tariff['meter_end'] ?? null;

                return [
                    'meter_start' => (float) $tariff['meter_start'],
                    'meter_end' => $meterEnd === null || $meterEnd === '' ? null : (float) $meterEnd,
                    'price' => (float) $tariff['price'],
                ];
            })
            ->values()
            ->all();
    }
}

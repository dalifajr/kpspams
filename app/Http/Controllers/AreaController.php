<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignAreaPetugasRequest;
use App\Http\Requests\StoreAreaRequest;
use App\Http\Requests\UpdateAreaRequest;
use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AreaController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        $areas = Area::with('petugas')->orderBy('name')->get();

        return Inertia::render('Areas/Index', [
            'areas' => $areas,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        return Inertia::render('Areas/Create');
    }


    public function store(StoreAreaRequest $request): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $area = Area::create([
            'name' => $request->string('name')->trim()->toString(),
            'slug' => $this->generateSlug($request->string('name')->toString()),
            'customer_count' => $request->integer('customer_count'),
            'notes' => $request->filled('notes') ? $request->string('notes')->trim()->toString() : null,
        ]);

        ChangeLog::record($request->user(), 'area.create', 'Menambahkan area baru.', [
            'subject_type' => Area::class,
            'subject_id' => $area->id,
            'after' => $area->only(['id', 'name', 'slug', 'customer_count', 'notes']),
        ]);

        return redirect()->route('menu.area.show', $area)->with('status', 'Area baru berhasil dibuat.');
    }

    public function show(Request $request, Area $area): Response
    {
        $this->ensureAdmin($request->user());

        $area->load('petugas');
        $petugasOptions = User::query()
            ->where('role', User::ROLE_PETUGAS)
            ->whereNotIn('id', $area->petugas->pluck('id'))
            ->orderBy('name')
            ->get();

        return Inertia::render('Areas/Show', [
            'area' => $area,
            'petugasOptions' => $petugasOptions,
        ]);
    }

    public function edit(Request $request, Area $area): Response
    {
        $this->ensureAdmin($request->user());

        return Inertia::render('Areas/Edit', [
            'area' => $area,
        ]);
    }

    public function update(UpdateAreaRequest $request, Area $area): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $before = $area->only(['name', 'slug', 'customer_count', 'notes']);

        $area->update([
            'name' => $request->string('name')->trim()->toString(),
            'slug' => $this->generateSlug($request->string('name')->toString(), $area->id),
            'customer_count' => $request->integer('customer_count'),
            'notes' => $request->filled('notes') ? $request->string('notes')->trim()->toString() : null,
        ]);

        ChangeLog::record($request->user(), 'area.update', 'Memperbarui data area.', [
            'subject_type' => Area::class,
            'subject_id' => $area->id,
            'before' => $before,
            'after' => $area->only(['name', 'slug', 'customer_count', 'notes']),
            'undo' => [
                'type' => 'update',
                'model' => Area::class,
                'id' => $area->id,
                'data' => $before,
            ],
        ]);

        return redirect()->route('menu.area.show', $area)->with('status', 'Data area berhasil diperbarui.');
    }

    public function destroy(Request $request, Area $area): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $before = $area->only(['id', 'name', 'slug']);

        $area->delete();

        ChangeLog::record($request->user(), 'area.delete', 'Menghapus area.', [
            'subject_type' => Area::class,
            'subject_id' => $area->id,
            'before' => $before,
        ]);

        return redirect()->route('menu.area')->with('status', 'Area berhasil dihapus.');
    }

    public function assignPetugas(AssignAreaPetugasRequest $request, Area $area): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $petugas = User::where('role', User::ROLE_PETUGAS)->findOrFail($request->integer('user_id'));

        $area->petugas()->syncWithoutDetaching([$petugas->id]);

        ChangeLog::record($request->user(), 'area.assign-petugas', 'Menambahkan petugas ke area.', [
            'subject_type' => Area::class,
            'subject_id' => $area->id,
            'after' => [
                'petugas_id' => $petugas->id,
                'petugas_name' => $petugas->name,
            ],
        ]);

        return redirect()->route('menu.area.show', $area)->with('status', 'Petugas berhasil ditambahkan ke area.');
    }

    public function removePetugas(Request $request, Area $area, User $petugas): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_unless($petugas->role === User::ROLE_PETUGAS, 404);

        $area->petugas()->detach($petugas->id);

        ChangeLog::record($request->user(), 'area.remove-petugas', 'Menghapus petugas dari area.', [
            'subject_type' => Area::class,
            'subject_id' => $area->id,
            'before' => [
                'petugas_id' => $petugas->id,
                'petugas_name' => $petugas->name,
            ],
        ]);

        return redirect()->route('menu.area.show', $area)->with('status', 'Petugas dihapus dari area.');
    }

    private function ensureAdmin(?User $user): void
    {
        abort_if(! $user || ! $user->isAdmin(), 403);
    }

    private function generateSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Area::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . (++$counter);
        }

        return $slug;
    }
}

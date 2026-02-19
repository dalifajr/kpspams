<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagedUserRequest;
use App\Http\Requests\UpdateManagedUserRequest;
use App\Http\Requests\UpdateManagedUserPasswordRequest;
use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        $search = (string) $request->query('q', '');
        $tab = $request->query('tab', 'data');
        $roleFilter = $request->query('role', 'all');
        $sort = $request->query('sort', 'az');
        $areaFilter = $request->query('area', 'all');
        $groupFilter = $request->query('group', 'all');

        $userQuery = User::query()
            ->where('id', '!=', $request->user()->id)
            ->whereIn('role', [User::ROLE_USER, User::ROLE_PETUGAS])
            ->where('status', User::STATUS_APPROVED);

        if ($search !== '') {
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if (in_array($roleFilter, [User::ROLE_USER, User::ROLE_PETUGAS], true)) {
            $userQuery->where('role', $roleFilter);
        }

        if ($areaFilter !== 'all' && $areaFilter !== null) {
            $userQuery->where('area_id', (int) $areaFilter);
        }

        if ($groupFilter !== 'all' && $groupFilter !== null) {
            $userQuery->whereRaw('1 = 0');
        }

        if ($sort === 'za') {
            $userQuery->orderBy('name', 'desc');
        } else {
            $userQuery->orderBy('name');
        }
        $users = $userQuery->get();

        $areasCollection = Area::orderBy('name')->get(['id', 'name']);
        $pendingUsers = User::query()
            ->where('status', User::STATUS_PENDING)
            ->where('id', '!=', $request->user()->id)
            ->where('role', User::ROLE_USER)
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'search' => $search,
            'tab' => $tab,
            'filters' => [
                'role' => $roleFilter,
                'sort' => $sort,
                'area' => $areaFilter,
                'group' => $groupFilter,
            ],
            'areas' => $areasCollection,
            'pendingUsers' => $pendingUsers,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureAdmin($request->user());

        return Inertia::render('Users/Create', [
            'areas' => Area::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, User $managedUser): Response
    {
        $this->ensureAdmin($request->user());

        return Inertia::render('Users/Show', [
            'managedUser' => $managedUser,
            'areas' => Area::orderBy('name')->get(['id', 'name']),
            'whatsappLink' => $this->buildWhatsappLink($managedUser),
        ]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        $avatarPath = $request->file('avatar')?->store('avatars', 'public');
        $area = Area::findOrFail($request->integer('area_id'));

        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone_number' => $request->string('phone_number')->toString(),
            'password' => $request->string('password')->toString(),
            'role' => User::ROLE_USER,
            'area_id' => $area->id,
            'status' => User::STATUS_APPROVED,
            'area' => $area->name,
            'address_short' => $request->filled('address_short') ? $request->string('address_short')->trim()->toString() : null,
            'avatar_path' => $avatarPath,
            'approved_at' => now(),
        ]);

        ChangeLog::record($request->user(), 'user.create', 'Membuat pengguna baru.', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'after' => $user->only(['id', 'name', 'email', 'phone_number', 'role', 'status', 'area_id']),
        ]);

        return redirect()->route('menu.user')->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function updateRole(Request $request, User $managedUser): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_if($managedUser->id === $request->user()->id, 403, 'Tidak dapat mengubah akun sendiri.');

        $before = $managedUser->only(['role']);

        $data = $request->validate([
            'role' => 'required|in:' . implode(',', [User::ROLE_USER, User::ROLE_PETUGAS]),
        ]);

        $managedUser->update(['role' => $data['role']]);

        ChangeLog::record($request->user(), 'user.update-role', 'Mengubah peran pengguna.', [
            'subject_type' => User::class,
            'subject_id' => $managedUser->id,
            'before' => $before,
            'after' => $managedUser->only(['role']),
            'undo' => [
                'type' => 'update',
                'model' => User::class,
                'id' => $managedUser->id,
                'data' => $before,
            ],
        ]);

        return redirect()->route('menu.user.show', $managedUser)->with('status', 'Hak akses pengguna diperbarui.');
    }

    public function update(UpdateManagedUserRequest $request, User $managedUser): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_if($managedUser->id === $request->user()->id, 403, 'Tidak dapat mengubah akun sendiri.');

        $before = $managedUser->only(['name', 'email', 'phone_number', 'area_id', 'area', 'address_short']);

        if ($request->hasFile('avatar')) {
            if ($managedUser->avatar_path) {
                Storage::disk('public')->delete($managedUser->avatar_path);
            }
            $managedUser->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $area = Area::findOrFail($request->integer('area_id'));

        $managedUser->update([
            'name' => $request->string('name')->trim()->toString(),
            'email' => $request->string('email')->trim()->toString(),
            'phone_number' => $request->string('phone_number')->trim()->toString(),
            'area_id' => $area->id,
            'area' => $area->name,
            'address_short' => $request->filled('address_short') ? $request->string('address_short')->trim()->toString() : null,
            'avatar_path' => $managedUser->avatar_path,
        ]);

        ChangeLog::record($request->user(), 'user.update', 'Memperbarui data pengguna.', [
            'subject_type' => User::class,
            'subject_id' => $managedUser->id,
            'before' => $before,
            'after' => $managedUser->only(['name', 'email', 'phone_number', 'area_id', 'area', 'address_short']),
            'undo' => [
                'type' => 'update',
                'model' => User::class,
                'id' => $managedUser->id,
                'data' => $before,
            ],
        ]);

        return redirect()->route('menu.user.show', $managedUser)->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $managedUser): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_if($managedUser->id === $request->user()->id, 403, 'Tidak dapat menghapus akun sendiri.');

        $before = $managedUser->only(['id', 'name', 'email', 'phone_number', 'role', 'status']);

        if ($managedUser->avatar_path) {
            Storage::disk('public')->delete($managedUser->avatar_path);
        }

        $managedUser->delete();

        ChangeLog::record($request->user(), 'user.delete', 'Menghapus pengguna.', [
            'subject_type' => User::class,
            'subject_id' => $managedUser->id,
            'before' => $before,
        ]);

        return redirect()->route('menu.user')->with('status', 'Pengguna berhasil dihapus.');
    }

    public function updatePassword(UpdateManagedUserPasswordRequest $request, User $managedUser): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_if($managedUser->id === $request->user()->id, 403, 'Tidak dapat mengubah password akun sendiri di menu ini.');

        $managedUser->update([
            'password' => $request->string('password')->toString(),
        ]);

        ChangeLog::record($request->user(), 'user.update-password', 'Memperbarui password pengguna.', [
            'subject_type' => User::class,
            'subject_id' => $managedUser->id,
        ]);

        return redirect()->route('menu.user.show', $managedUser)->with('status', 'Password pengguna berhasil diperbarui.');
    }

    public function approve(Request $request, User $managedUser): RedirectResponse
    {
        $this->ensureAdmin($request->user());

        abort_if(! $managedUser->isPending(), 404, 'User tidak membutuhkan persetujuan.');
        abort_if(! $managedUser->isUser(), 404, 'Hanya pengguna biasa yang dapat disetujui.');

        $before = $managedUser->only(['status', 'approved_at']);

        $managedUser->forceFill([
            'status' => User::STATUS_APPROVED,
            'approved_at' => now(),
        ])->save();

        ChangeLog::record($request->user(), 'user.approve', 'Menyetujui pengguna baru.', [
            'subject_type' => User::class,
            'subject_id' => $managedUser->id,
            'before' => $before,
            'after' => $managedUser->only(['status', 'approved_at']),
            'undo' => [
                'type' => 'update',
                'model' => User::class,
                'id' => $managedUser->id,
                'data' => $before,
            ],
        ]);

        $statusMessage = 'User berhasil disetujui.';

        if ($request->boolean('notify')) {
            if ($link = $this->buildWhatsappLink($managedUser)) {
                session()->flash('whatsapp_link', $link);
                $statusMessage = 'User disetujui. Mengarahkan ke WhatsApp...';
            }
        }

        return redirect()->route('menu.user', ['tab' => 'confirm'])->with('status', $statusMessage);
    }

    private function ensureAdmin(?User $user): void
    {
        abort_if(! $user || ! $user->isAdmin(), 403);
    }

    private function buildWhatsappLink(?User $user): ?string
    {
        if (! $user || ! $user->phone_number) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $user->phone_number);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (! str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        $appUrl = config('app.url') ?: url('/');
        $message = rawurlencode("Halo {$user->name}, akun MeterPAMS Anda sudah aktif. Silakan login di {$appUrl}.");

        return "https://wa.me/{$digits}?text={$message}";
    }
}

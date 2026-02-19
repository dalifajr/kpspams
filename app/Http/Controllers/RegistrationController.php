<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\Area;
use App\Models\ChangeLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegistrationController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'areas' => Area::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(RegisterUserRequest $request): RedirectResponse
    {
        $avatarPath = $request->file('avatar')?->store('avatars', 'public');
        $area = Area::findOrFail($request->integer('area_id'));

        $phoneNumber = $request->string('phone_number')->trim()->toString();

        $user = User::create([
            'name' => $request->string('name')->trim()->toString(),
            'email' => $this->generateEmailFromPhone($phoneNumber),
            'phone_number' => $phoneNumber,
            'password' => $request->string('password')->toString(),
            'role' => User::ROLE_USER,
            'status' => User::STATUS_PENDING,
            'area_id' => $area->id,
            'area' => $area->name,
            'address_short' => $request->filled('address_short') ? $request->string('address_short')->trim()->toString() : null,
            'avatar_path' => $avatarPath,
            'approved_at' => null,
        ]);

        ChangeLog::record($user, 'user.register', 'Pengguna mendaftar akun.', [
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'after' => $user->only(['id', 'name', 'phone_number', 'status', 'area_id']),
        ]);

        return redirect()->route('login')->with('status', 'Registrasi berhasil. Akun Anda menunggu persetujuan admin.');
    }

    private function generateEmailFromPhone(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?: Str::random(12);

        return "{$digits}@user.local";
    }
}

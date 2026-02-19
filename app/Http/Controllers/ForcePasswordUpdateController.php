<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForcePasswordUpdateController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->must_update_password) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/ForcePassword');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user || ! $user->must_update_password) {
            return redirect()->route('dashboard');
        }

        $data = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => $data['password'],
            'must_update_password' => false,
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Kata sandi berhasil diperbarui.');
    }
}

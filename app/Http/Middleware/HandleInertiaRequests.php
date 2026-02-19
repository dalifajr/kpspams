<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'layouts.app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'phone_number' => $request->user()->phone_number,
                    'role' => $request->user()->role,
                    'status' => $request->user()->status,
                    'avatar_path' => $request->user()->avatar_path,
                    'address_short' => $request->user()->address_short,
                    'is_admin' => $request->user()->isAdmin(),
                    'is_petugas' => $request->user()->isPetugas(),
                    'is_user' => $request->user()->isUser(),
                ] : null,
            ],
            'branding' => config('kpspams.branding'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'status' => fn () => $request->session()->get('status'),
            ],
        ];
    }
}

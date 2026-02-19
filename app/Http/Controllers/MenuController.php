<?php

namespace App\Http\Controllers;

use App\Support\MenuRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function show(Request $request, string $slug): \Inertia\Response|\Illuminate\Http\RedirectResponse
    {
        $menu = MenuRepository::find($slug);

        if (is_null($menu)) {
            abort(404);
        }

        $user = $request->user();
        $operatorSlugs = MenuRepository::operatorSlugs();
        $allowedSlugs = array_merge($operatorSlugs, MenuRepository::userSlugs());

        if (! $user->isAdmin() && ! in_array($slug, $allowedSlugs, true)) {
            abort(403);
        }

        return \Inertia\Inertia::render('Menu/Show', [
            'menu' => $menu,
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Support\MenuRepository;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $adminMenus = $user->isAdmin() ? MenuRepository::admin() : [];
        $operatorMenus = ($user->isAdmin() || $user->isPetugas()) ? MenuRepository::operator() : [];
        $consumerMenus = $user->isUser() ? MenuRepository::user() : [];

        return Inertia::render('Dashboard', [
            'adminMenus' => $adminMenus,
            'operatorMenus' => $operatorMenus,
            'consumerMenus' => $consumerMenus,
            'showAdminSection' => $user->isAdmin(),
        ]);
    }
}

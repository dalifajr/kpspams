<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    /**
     * Display a listing of areas accessible by the user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Area::query();

        if ($user->isPetugas()) {
            $areaIds = $user->assignedAreas()->pluck('areas.id');
            if ($areaIds->isNotEmpty()) {
                $query->whereIn('id', $areaIds);
            } elseif ($user->area_id) {
                // Jatuh kembali ke tabel users area_id jika petugas tidak punya area dinamis assignation
                $query->where('id', $user->area_id);
            } else {
                return response()->json([]); // Tidak punya area
            }
        } elseif ($user->isUser()) {
             if ($user->area_id) {
                $query->where('id', $user->area_id);
             } else {
                 return response()->json([]); // User blm set area
             }
        }
        // admin melihat semua

        $areas = $query->orderBy('name')->get();

        return response()->json($areas);
    }
}

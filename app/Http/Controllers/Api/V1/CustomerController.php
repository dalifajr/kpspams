<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->with(['area', 'golongan'])
            ->forUser($request->user())
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%");
            })
            ->when($request->area_id, function ($query, $areaId) {
                $query->where('area_id', $areaId);
            })
            ->orderBy('name')
            ->paginate($request->integer('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Customer $customer): JsonResponse
    {
        // Pastikan customer yang diakses ada dalam scope user saat ini
        $isAccessible = Customer::query()
            ->forUser($request->user())
            ->where('id', $customer->id)
            ->exists();

        if (!$isAccessible) {
            return response()->json(['message' => 'Unauthorized or Not Found'], 404);
        }

        $customer->load(['area', 'golongan']);

        return response()->json([
            'data' => $customer,
        ]);
    }
}

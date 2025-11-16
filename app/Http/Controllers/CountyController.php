<?php

namespace App\Http\Controllers;

use App\Models\County;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountyController extends Controller
{
    /**
     * Fetch all counties without cities.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $needle = $request->query('needle');

        $query = County::query();
        if ($needle) {
            $needle = mb_strtolower($needle);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$needle}%"]);
        }

        $counties = $query->get(['id', 'name']);
        return response()->json($counties);
    }

    /**
     * Store a new county.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:counties,name',
        ]);

        $county = County::create(['name' => $validated['name']]);

        return response()->json($county, 201);
    }

    /**
     * Update a county by id.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $county = County::find($id);
        if (!$county) {
            return response()->json(['message' => 'Not found!'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|unique:counties,name,' . $id,
        ]);

        $county->name = $validated['name'];
        $county->save();

        return response()->json($county);
    }

    /**
     * Delete a county by id.
     */
    public function destroy(int $id): JsonResponse
    {
        $county = County::find($id);
        if (!$county) {
            return response()->json(['message' => 'Not found!'], 404);
        }

        $county->delete();
        return response()->json(['message' => 'Deleted'], 410);
    }
}
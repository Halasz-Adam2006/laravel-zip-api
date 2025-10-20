<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostalCode;

class PostalCodeController extends Controller
{



    public function store(Request $request)
    {
        $validated = $request->validate([
            'zip' => 'required|string',
            'city' => 'required|string',
            'county' => 'required|string',
        ]);

        $postalCode = PostalCode::create($validated);

        return response()->json($postalCode, 201);
    }

    public function destroy(string $id)
    {
        $postalCode = PostalCode::where('zip', $id)->first();

        if ($postalCode) {
            $postalCode->delete();
            return response()->json(['message' => 'Postal code deleted successfully'], 204);
        } else {
            return response()->json(['message' => 'Postal code not found'], 404);
        }
    }

    public function show(string $id)
    {
        $postalCodes = PostalCode::where('zip', $id)->get();

        return response()->json($postalCodes);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $postalCode = PostalCode::where('zip', $id)->first();

        if ($postalCode) {
            $postalCode->update($validated);
            return response()->json($postalCode);
        } else {
            return response()->json(['message' => 'Postal code not found'], 404);
        }
    }

    public function showById(int $id)
    {
        $postalCode = PostalCode::find($id);

        if ($postalCode) {
            return response()->json($postalCode);
        }

        return response()->json(['message' => 'Postal code not found'], 404);
    }

    public function destroyById(int $id)
    {
        $postalCode = PostalCode::find($id);

        if ($postalCode) {
            $postalCode->delete();
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Postal code not found'], 404);
    }

    public function updateById(Request $request, int $id)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $postalCode = PostalCode::find($id);

        if ($postalCode) {
            $postalCode->update($validated);
            return response()->json($postalCode);
        }

        return response()->json(['message' => 'Postal code not found'], 404);
    }

    public function showByCity(string $city)
    {
        $postalCodes = PostalCode::where('city', $city)->get();

        return response()->json($postalCodes);
    }

    public function destroyByCity(string $city)
    {
        $deleted = PostalCode::where('city', $city)->delete();

        if ($deleted) {
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Postal codes not found for given city'], 404);
    }

    public function updateByCity(Request $request, string $city)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $affected = PostalCode::where('city', $city)->update($validated);

        if ($affected) {
            $updated = PostalCode::where('city', $validated['city'] ?? $city)->get();
            return response()->json($updated);
        }

        return response()->json(['message' => 'Postal codes not found for given city'], 404);
    }


    public function showByCounty(string $county)
    {
        $postalCodes = PostalCode::where('county', $county)->get();

        return response()->json($postalCodes);
    }

    public function destroyByCounty(string $county)
    {
        $deleted = PostalCode::where('county', $county)->delete();

        if ($deleted) {
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Postal codes not found for given county'], 404);
    }

    public function updateByCounty(Request $request, string $county)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $affected = PostalCode::where('county', $county)->update($validated);

        if ($affected) {
            $updated = PostalCode::where('county', $validated['county'] ?? $county)->get();
            return response()->json($updated);
        }

        return response()->json(['message' => 'Postal codes not found for given county'], 404);
    }
}

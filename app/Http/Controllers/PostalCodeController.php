<?php

namespace App\Http\Controllers;

use App\Models\PostalCode;
use Illuminate\Http\Request;

class PostalCodeController extends Controller
{
    public function manage()
    {
        return view('postal-codes.manage');
    }

    public function index(Request $request)
    {
        $query = trim((string) $request->query('query', ''));
        $limit = (int) $request->query('limit', 20);
        $limit = max(1, min($limit, 50));

        $results = PostalCode::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder
                    ->where('postal_code', 'like', $query . '%')
                    ->orWhere('place_name', 'like', '%' . $query . '%')
                    ->orWhere('county', 'like', '%' . $query . '%');
            })
            ->orderBy('postal_code')
            ->limit($limit)
            ->get(['id', 'postal_code', 'place_name', 'county']);

        return response()->json([
            'query' => $query,
            'count' => $results->count(),
            'data' => $results,
        ]);
    }

    public function show(string $postalCode)
    {
        $results = PostalCode::query()
            ->where('postal_code', $postalCode)
            ->orderBy('place_name')
            ->get(['id', 'postal_code', 'place_name', 'county']);

        return response()->json([
            'postal_code' => $postalCode,
            'count' => $results->count(),
            'data' => $results,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'postal_code' => ['required', 'string', 'max:10'],
            'place_name' => ['required', 'string', 'max:255'],
            'county' => ['required', 'string', 'max:255'],
        ]);

        $postalCode = PostalCode::create($validated);

        return response()->json([
            'message' => 'Postal code created successfully.',
            'data' => $postalCode,
        ], 201);
    }

    public function update(Request $request, PostalCode $postalCode)
    {
        $validated = $request->validate([
            'postal_code' => ['sometimes', 'required', 'string', 'max:10'],
            'place_name' => ['sometimes', 'required', 'string', 'max:255'],
            'county' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        if (count($validated) === 0) {
            return response()->json([
                'message' => 'No fields provided for update.',
            ], 422);
        }

        $postalCode->fill($validated);
        $postalCode->save();

        return response()->json([
            'message' => 'Postal code updated successfully.',
            'data' => $postalCode,
        ]);
    }

    public function destroy(PostalCode $postalCode)
    {
        $postalCode->delete();

        return response()->json([
            'message' => 'Postal code deleted successfully.',
        ]);
    }
}

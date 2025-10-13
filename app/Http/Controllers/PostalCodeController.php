<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostalCode;

class PostalCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // You can implement this to return all postal codes if needed
    }

    /**
     * Store a newly created resource in storage.
     */
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


}

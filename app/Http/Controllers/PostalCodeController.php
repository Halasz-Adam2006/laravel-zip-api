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
        // Implement the logic to store a new postal code
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
        $postalCodes = PostalCode::where('zip', $id)->get();


        
        return response()->json($postalCodes);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // You can implement the logic to update a postal code
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // You can implement the logic to delete a postal code
    }
}

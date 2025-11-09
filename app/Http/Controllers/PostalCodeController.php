<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostalCode;
use App\Models\County;

class PostalCodeController extends Controller
{
    /**
     * Get all postal codes.
     *
     * Returns a list of all postal codes with their associated county information.
     *
     * @group Postal Codes
     * @authenticated
     * @response 200 [{
     *   "id": 1,
     *   "zip": "1011",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }]
     */
    public function index()
    {
        $postalCodes = PostalCode::with('county')->get();
        return response()->json($postalCodes);
    }

    /**
     * Create a new postal code.
     *
     * Creates a new postal code record. If the county doesn't exist, it will be created automatically.
     *
     * @group Postal Codes
     * @authenticated
     * @bodyParam zip string required The postal code (zip). Example: 1011
     * @bodyParam city string required The city name. Example: Budapest
     * @bodyParam county string required The county name. Example: Budapest
     * @response 201 {
     *   "id": 1,
     *   "zip": "1011",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'zip' => 'required|string',
            'city' => 'required|string',
            'county' => 'required|string',
        ]);

        // Find or create the county
        $county = County::firstOrCreate(['name' => $validated['county']]);

        $postalCode = PostalCode::create([
            'zip' => $validated['zip'],
            'city' => $validated['city'],
            'county_id' => $county->id,
        ]);

        // Load the county relationship for the response
        $postalCode->load('county');

        return response()->json($postalCode, 201);
    }

    /**
     * Delete a postal code by zip.
     *
     * Deletes the first postal code matching the given zip value.
     *
     * @group Postal Codes
     * @authenticated
     * @urlParam id string required The zip code. Example: 1011
     * @response 204 scenario="Deleted successfully"
     * @response 404 {
     *   "message": "Postal code not found"
     * }
     */
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

    /**
     * Get postal codes by zip.
     *
     * Returns all postal codes matching the given zip value.
     *
     * @group Postal Codes
     * @authenticated
     * @urlParam id string required The zip code. Example: 1011
     * @response 200 [{
     *   "id": 1,
     *   "zip": "1011",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }]
     */
    public function show(string $id)
    {
        $postalCodes = PostalCode::with('county')->where('zip', $id)->get();

        return response()->json($postalCodes);
    }

    /**
     * Update a postal code by zip.
     *
     * Updates the first postal code matching the given zip value. All fields are optional.
     *
     * @group Postal Codes
     * @authenticated
     * @urlParam id string required The zip code to update. Example: 1011
     * @bodyParam zip string optional New zip code. Example: 1012
     * @bodyParam city string optional New city name. Example: Budapest
     * @bodyParam county string optional New county name. Example: Pest
     * @response 200 {
     *   "id": 1,
     *   "zip": "1012",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }
     * @response 404 {
     *   "message": "Postal code not found"
     * }
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $postalCode = PostalCode::where('zip', $id)->first();

        if (!$postalCode) {
            return response()->json(['message' => 'Postal code not found'], 404);
        }

        // Update county if provided
        if (isset($validated['county'])) {
            $county = County::firstOrCreate(['name' => $validated['county']]);
            $postalCode->county_id = $county->id;
        }

        if (isset($validated['zip'])) {
            $postalCode->zip = $validated['zip'];
        }
        
        if (isset($validated['city'])) {
            $postalCode->city = $validated['city'];
        }

        $postalCode->save();
        $postalCode->load('county');

        return response()->json($postalCode);
    }

    /**
     * Get a postal code by ID.
     *
     * Returns a single postal code by its database ID.
     *
     * @group Postal Codes
     * @authenticated
     * @urlParam id integer required The database ID. Example: 1
     * @response 200 {
     *   "id": 1,
     *   "zip": "1011",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }
     * @response 404 {
     *   "message": "Postal code not found"
     * }
     */
    public function showById(int $id)
    {
        $postalCode = PostalCode::with('county')->find($id);

        if ($postalCode) {
            return response()->json($postalCode);
        }

        return response()->json(['message' => 'Postal code not found'], 404);
    }

    /**
     * Delete a postal code by ID.
     *
     * Deletes a postal code by its database ID.
     *
     * @group Postal Codes
     * @authenticated
     * @urlParam id integer required The database ID. Example: 1
     * @response 204 scenario="Deleted successfully"
     * @response 404 {
     *   "message": "Postal code not found"
     * }
     */
    public function destroyById(int $id)
    {
        $postalCode = PostalCode::find($id);

        if ($postalCode) {
            $postalCode->delete();
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Postal code not found'], 404);
    }

    /**
     * Update a postal code by ID.
     *
     * Updates a postal code by its database ID. All fields are optional.
     *
     * @group Postal Codes
     * @authenticated
     * @urlParam id integer required The database ID. Example: 1
     * @bodyParam zip string optional New zip code. Example: 1012
     * @bodyParam city string optional New city name. Example: Budapest
     * @bodyParam county string optional New county name. Example: Pest
     * @response 200 {
     *   "id": 1,
     *   "zip": "1012",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }
     * @response 404 {
     *   "message": "Postal code not found"
     * }
     */
    public function updateById(Request $request, int $id)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $postalCode = PostalCode::find($id);

        if (!$postalCode) {
            return response()->json(['message' => 'Postal code not found'], 404);
        }

        // Update county if provided
        if (isset($validated['county'])) {
            $county = County::firstOrCreate(['name' => $validated['county']]);
            $postalCode->county_id = $county->id;
        }

        if (isset($validated['zip'])) {
            $postalCode->zip = $validated['zip'];
        }
        
        if (isset($validated['city'])) {
            $postalCode->city = $validated['city'];
        }

        $postalCode->save();
        $postalCode->load('county');

        return response()->json($postalCode);
    }

    /**
     * Get postal codes by city.
     *
     * Returns all postal codes for a given city name.
     *
     * @group Postal Codes - City
     * @authenticated
     * @urlParam city string required The city name. Example: Budapest
     * @response 200 [{
     *   "id": 1,
     *   "zip": "1011",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }]
     */
    public function showByCity(string $city)
    {
        $postalCodes = PostalCode::with('county')->where('city', $city)->get();

        return response()->json($postalCodes);
    }

    /**
     * Delete postal codes by city.
     *
     * Deletes all postal codes for a given city name.
     *
     * @group Postal Codes - City
     * @authenticated
     * @urlParam city string required The city name. Example: Budapest
     * @response 204 scenario="Deleted successfully"
     * @response 404 {
     *   "message": "Postal codes not found for given city"
     * }
     */
    public function destroyByCity(string $city)
    {
        $deleted = PostalCode::where('city', $city)->delete();

        if ($deleted) {
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Postal codes not found for given city'], 404);
    }

    /**
     * Update postal codes by city.
     *
     * Updates all postal codes for a given city name. All fields are optional.
     *
     * @group Postal Codes - City
     * @authenticated
     * @urlParam city string required The city name. Example: Budapest
     * @bodyParam zip string optional New zip code. Example: 1012
     * @bodyParam city string optional New city name. Example: Debrecen
     * @bodyParam county string optional New county name. Example: Pest
     * @response 200 [{
     *   "id": 1,
     *   "zip": "1012",
     *   "city": "Debrecen",
     *   "county_id": 2,
     *   "county": {
     *     "id": 2,
     *     "name": "Pest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }]
     * @response 404 {
     *   "message": "Postal codes not found for given city"
     * }
     */
    public function updateByCity(Request $request, string $city)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $postalCodes = PostalCode::where('city', $city)->get();

        if ($postalCodes->isEmpty()) {
            return response()->json(['message' => 'Postal codes not found for given city'], 404);
        }

        // Update county if provided
        $countyId = null;
        if (isset($validated['county'])) {
            $county = County::firstOrCreate(['name' => $validated['county']]);
            $countyId = $county->id;
        }

        foreach ($postalCodes as $postalCode) {
            if ($countyId) {
                $postalCode->county_id = $countyId;
            }
            if (isset($validated['zip'])) {
                $postalCode->zip = $validated['zip'];
            }
            if (isset($validated['city'])) {
                $postalCode->city = $validated['city'];
            }
            $postalCode->save();
        }

        $updated = PostalCode::with('county')->where('city', $validated['city'] ?? $city)->get();
        return response()->json($updated);
    }


    /**
     * Get postal codes by county.
     *
     * Returns all postal codes for a given county name.
     *
     * @group Postal Codes - County
     * @authenticated
     * @urlParam county string required The county name. Example: Pest
     * @response 200 [{
     *   "id": 1,
     *   "zip": "1011",
     *   "city": "Budapest",
     *   "county_id": 1,
     *   "county": {
     *     "id": 1,
     *     "name": "Pest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }]
     */
    public function showByCounty(string $county)
    {
        $countyModel = County::where('name', $county)->first();
        
        if (!$countyModel) {
            return response()->json([]);
        }

        $postalCodes = PostalCode::with('county')->where('county_id', $countyModel->id)->get();

        return response()->json($postalCodes);
    }

    /**
     * Delete postal codes by county.
     *
     * Deletes all postal codes for a given county name.
     *
     * @group Postal Codes - County
     * @authenticated
     * @urlParam county string required The county name. Example: Pest
     * @response 204 scenario="Deleted successfully"
     * @response 404 {
     *   "message": "Postal codes not found for given county"
     * }
     */
    public function destroyByCounty(string $county)
    {
        $countyModel = County::where('name', $county)->first();
        
        if (!$countyModel) {
            return response()->json(['message' => 'Postal codes not found for given county'], 404);
        }

        $deleted = PostalCode::where('county_id', $countyModel->id)->delete();

        if ($deleted) {
            return response()->json(null, 204);
        }

        return response()->json(['message' => 'Postal codes not found for given county'], 404);
    }

    /**
     * Update postal codes by county.
     *
     * Updates all postal codes for a given county name. All fields are optional.
     *
     * @group Postal Codes - County
     * @authenticated
     * @urlParam county string required The county name. Example: Pest
     * @bodyParam zip string optional New zip code. Example: 1012
     * @bodyParam city string optional New city name. Example: Budapest
     * @bodyParam county string optional New county name. Example: Budapest
     * @response 200 [{
     *   "id": 1,
     *   "zip": "1012",
     *   "city": "Budapest",
     *   "county_id": 2,
     *   "county": {
     *     "id": 2,
     *     "name": "Budapest"
     *   },
     *   "created_at": "2025-01-01T00:00:00.000000Z",
     *   "updated_at": "2025-01-01T00:00:00.000000Z"
     * }]
     * @response 404 {
     *   "message": "Postal codes not found for given county"
     * }
     */
    public function updateByCounty(Request $request, string $county)
    {
        $validated = $request->validate([
            'zip' => 'sometimes|required|string',
            'city' => 'sometimes|required|string',
            'county' => 'sometimes|required|string',
        ]);

        $countyModel = County::where('name', $county)->first();
        
        if (!$countyModel) {
            return response()->json(['message' => 'Postal codes not found for given county'], 404);
        }

        $postalCodes = PostalCode::where('county_id', $countyModel->id)->get();

        if ($postalCodes->isEmpty()) {
            return response()->json(['message' => 'Postal codes not found for given county'], 404);
        }

        // Update county if provided
        $newCountyId = null;
        if (isset($validated['county'])) {
            $newCounty = County::firstOrCreate(['name' => $validated['county']]);
            $newCountyId = $newCounty->id;
        }

        foreach ($postalCodes as $postalCode) {
            if ($newCountyId) {
                $postalCode->county_id = $newCountyId;
            }
            if (isset($validated['zip'])) {
                $postalCode->zip = $validated['zip'];
            }
            if (isset($validated['city'])) {
                $postalCode->city = $validated['city'];
            }
            $postalCode->save();
        }

        $targetCountyId = $newCountyId ?? $countyModel->id;
        $updated = PostalCode::with('county')->where('county_id', $targetCountyId)->get();
        return response()->json($updated);
    }
}

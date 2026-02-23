<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PostalCode;
use App\Models\County;
use Barryvdh\DomPDF\Facade\Pdf;

class PostalCodeController extends Controller
{

    public function index()
    {
        $postalCodes = PostalCode::with('county')->get();
        return response()->json($postalCodes);
    }

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
        $postalCodes = PostalCode::with('county')->where('zip', $id)->get();

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

    public function showById(int $id)
    {
        $postalCode = PostalCode::with('county')->find($id);

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


    public function showByCity(string $city)
    {
        $postalCodes = PostalCode::with('county')->where('city', $city)->get();

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


    public function showByCounty(string $county)
    {
        $countyModel = County::where('name', $county)->first();

        if (!$countyModel) {
            return response()->json([]);
        }

        $postalCodes = PostalCode::with('county')->where('county_id', $countyModel->id)->get();

        return response()->json($postalCodes);
    }

    public function showByLetter(string $county, string $letter)
    {
        $countyModel = County::whereRaw('LOWER(name) = ?', [strtolower($county)])->first();

        if (!$countyModel) {
            return response()->json([]);
        }

        $postalCodes = PostalCode::with('county')
            ->where('county_id', $countyModel->id)
            ->whereRaw('LOWER(city) LIKE ?', [strtolower($letter) . '%'])
            ->get();

        return response()->json($postalCodes);
    }


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

        //update
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

    public function exportCitiesPdf($name, $letter)
    {
        $letter = strtoupper($letter);

        $county = County::where('name', $name)->first();
        if (!$county) {
            return response()->json(['error' => 'County not found'], 404);
        }

        $cities = $county->postalCodes()
            ->where('city', 'LIKE', $letter . '%')
            ->orderBy('city')
            ->get();

        $pdf = Pdf::loadView('pdf.cities', [
            'county' => $county,
            'letter' => $letter,
            'cities' => $cities
        ]);

        return $pdf->download("cities_{$name}_{$letter}.pdf");
    }


    public function exportCitiesCsv($name, $letter)
    {
        $letter = strtoupper($letter);

        $county = County::where('name', $name)->first();
        if (!$county) {
            return response()->json(['error' => 'County not found'], 404);
        }

        $cities = $county->postalCodes()
            ->where('city', 'LIKE', $letter . '%')
            ->orderBy('city')
            ->get();

        $csv = "ID,Irányítószám,Város,Megye\n";
        foreach ($cities as $city) {
            $csv .= "{$city->id},{$city->zip},{$city->city},{$city->county->name}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"cities_{$name}_{$letter}.csv\"");
    }
}

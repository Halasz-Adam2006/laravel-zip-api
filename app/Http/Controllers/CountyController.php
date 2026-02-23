<?php

namespace App\Http\Controllers;

use App\Mail\AlphabetExportMail;
use App\Models\PostalCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CountyController extends Controller
{
    public function index(): View
    {
        return view('counties.index');
    }

    public function showAlphabet(string $name): View
    {
        $county = (object) ['name' => $name];

        return view('counties.alphabet', compact('county'));
    }

    public function showCounties(): JsonResponse
    {
        $counties = PostalCode::query()
            ->select('county')
            ->whereNotNull('county')
            ->distinct()
            ->orderBy('county')
            ->pluck('county')
            ->values();

        $payload = $counties->map(function ($county, $index) {
            return [
                'id' => $index + 1,
                'name' => $county,
            ];
        });

        return response()->json($payload);
    }

    public function getCitiesByLetter(string $name, string $letter): JsonResponse
    {
        $cities = $this->getAlphabetCities($name, $letter);

        if ($cities === null) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        return response()->json($cities->map(fn($city) => ['city' => $city]));
    }

    public function exportAlphabetCsv(string $name, string $letter)
    {
        $cities = $this->getAlphabetCities($name, $letter);

        if ($cities === null) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        $filename = sprintf('alphabet-%s-%s.csv', str_replace(' ', '-', strtolower($name)), strtolower($letter));
        $csv = $this->buildAlphabetCsv($name, $letter, $cities);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportAlphabetPdf(string $name, string $letter)
    {
        $cities = $this->getAlphabetCities($name, $letter);

        if ($cities === null) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        $pdf = Pdf::loadView('exports.alphabet-pdf', [
            'county' => $name,
            'letter' => strtoupper($letter),
            'cities' => $cities,
        ]);

        $filename = sprintf('alphabet-%s-%s.pdf', str_replace(' ', '-', strtolower($name)), strtolower($letter));

        return $pdf->download($filename);
    }

    public function exportAlphabetEmail(Request $request, string $name, string $letter): JsonResponse
    {
        $cities = $this->getAlphabetCities($name, $letter);

        if ($cities === null) {
            return response()->json(['error' => 'Invalid letter parameter'], 400);
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email'],
        ]);

        $recipient = $validated['email'] ?? $request->user()?->email;

        if (!$recipient) {
            return response()->json(['error' => 'No recipient email available.'], 422);
        }

        $csv = $this->buildAlphabetCsv($name, $letter, $cities);

        Mail::to($recipient)->send(new AlphabetExportMail(
            county: $name,
            letter: strtoupper($letter),
            cities: $cities->all(),
            csv: $csv,
        ));

        return response()->json([
            'message' => 'Export email sent successfully.',
        ]);
    }

    private function getAlphabetCities(string $name, string $letter): ?Collection
    {
        $letter = trim($letter);

        if (!preg_match('/^[A-Za-z]$/', $letter)) {
            return null;
        }

        return PostalCode::query()
            ->where('county', $name)
            ->where('place_name', 'like', $letter . '%')
            ->select('place_name')
            ->distinct()
            ->orderBy('place_name')
            ->pluck('place_name')
            ->values();
    }

    private function buildAlphabetCsv(string $name, string $letter, Collection $cities): string
    {
        $stream = fopen('php://temp', 'r+');
        fputcsv($stream, ['County', 'Letter', 'City']);

        foreach ($cities as $city) {
            fputcsv($stream, [$name, strtoupper($letter), $city]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv;
    }
}
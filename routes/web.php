<?php

use App\Http\Controllers\CountyController;
use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('counties.show');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/postal-codes/manage', [PostalCodeController::class, 'manage'])
        ->name('postal-codes.manage');

    Route::prefix('api')->group(function () {
        Route::post('/postal-codes', [PostalCodeController::class, 'store']);
        Route::patch('/postal-codes/{postalCode}', [PostalCodeController::class, 'update']);
        Route::delete('/postal-codes/{postalCode}', [PostalCodeController::class, 'destroy']);
    });
});

Route::get('/counties', [CountyController::class, 'index'])->name('counties.show');
Route::get('/counties/{name}/alphabet', [CountyController::class, 'showAlphabet'])->name('counties.alphabet');
Route::get('/counties/{name}/alphabet/{letter}/export/csv', [CountyController::class, 'exportAlphabetCsv'])
    ->where('letter', '[A-Za-z]');
Route::get('/counties/{name}/alphabet/{letter}/export/pdf', [CountyController::class, 'exportAlphabetPdf'])
    ->where('letter', '[A-Za-z]');
Route::post('/counties/{name}/alphabet/{letter}/export/email', [CountyController::class, 'exportAlphabetEmail'])
    ->middleware('auth')
    ->where('letter', '[A-Za-z]');

require __DIR__ . '/auth.php';

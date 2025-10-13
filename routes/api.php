<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostalCodeController;

Route::get('/user', function (Request $request) {
    return $request->user();
});
Route::get('postal-codes', [PostalCodeController::class, 'index']);
Route::get('postal-codes/{postal_code}', [PostalCodeController::class, 'show']);
/*Route::middleware('auth:sanctum')->group(function () {
    Route::post('postal-codes', [PostalCodeController::class, 'store']);
    Route::put('postal-codes/{postal_code}', [PostalCodeController::class, 'update']);
    Route::delete('postal-codes/{postal_code}', [PostalCodeController::class, 'destroy']);
});*/

Route::post('postal-codes', [PostalCodeController::class, 'store']);
Route::put('postal-codes/{postal_code}', [PostalCodeController::class, 'update']);
Route::delete('postal-codes/{postal_code}', [PostalCodeController::class, 'destroy']);

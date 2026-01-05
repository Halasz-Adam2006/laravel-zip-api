<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostalCodeController;
use App\Http\Controllers\CountyController;

// Public routes - no authentication required
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);

// Protected routes - authentication required
//Route::middleware('auth:sanctum')->group(function () {
   Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
    
    // Postal codes routes
    Route::get('postal-codes', [PostalCodeController::class, 'index']);
    Route::get('postal-codes/{postal_code}', [PostalCodeController::class, 'show']);
    Route::post('postal-codes', [PostalCodeController::class, 'store']);
    Route::put('postal-codes/{postal_code}', [PostalCodeController::class, 'update']);
    Route::delete('postal-codes/{postal_code}', [PostalCodeController::class, 'destroy']);
    
    // ID-based routes
    Route::get('id/{id}', [PostalCodeController::class, 'showById']);
    Route::put('id/{id}', [PostalCodeController::class, 'updateById']);
    Route::delete('id/{id}', [PostalCodeController::class, 'destroyById']);
    
    Route::get('city/{city}', [PostalCodeController::class, 'showByCity']);
    Route::put('city/{city}', [PostalCodeController::class, 'updateByCity']);
    Route::delete('city/{city}', [PostalCodeController::class, 'destroyByCity']);
    
    Route::get('county/{county}', [PostalCodeController::class, 'showByCounty']);
    Route::get('county/{county}/{letter}', [PostalCodeController::class, 'showByLetter']);
    Route::get('county/{county}/{letter}/export/pdf', [PostalCodeController::class, 'exportCitiesPdf']);
    Route::get('county/{county}/{letter}/export/csv', [PostalCodeController::class, 'exportCitiesCsv']);
    Route::put('county/{county}', [PostalCodeController::class, 'updateByCounty']);
    Route::delete('county/{county}', [PostalCodeController::class, 'destroyByCounty']);
    
    Route::get('counties', [CountyController::class, 'index']);
    Route::post('counties', [CountyController::class, 'store']);
    Route::put('counties/{id}', [CountyController::class, 'update']);
    Route::delete('counties/{id}', [CountyController::class, 'destroy']);

    
//})
;


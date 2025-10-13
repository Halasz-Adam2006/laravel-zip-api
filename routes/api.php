<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostalCodeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::get('postal-codes', [PostalCodeController::class, 'index']);
Route::get('postal-codes/{postal_code}', [PostalCodeController::class, 'show']);
route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);

Route::post('postal-codes', [PostalCodeController::class, 'store']) ->middleware('auth:sanctum');
Route::put('postal-codes/{postal_code}', [PostalCodeController::class, 'update']) ->middleware('auth:sanctum');
Route::delete('postal-codes/{postal_code}', [PostalCodeController::class, 'destroy']) ->middleware('auth:sanctum');

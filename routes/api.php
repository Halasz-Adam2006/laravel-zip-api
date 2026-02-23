<?php

use App\Http\Controllers\CountyController;
use App\Http\Controllers\PostalCodeController;
use Illuminate\Support\Facades\Route;

Route::get('/postal-codes', [PostalCodeController::class, 'index']);
Route::get('/postal-codes/{postalCode}', [PostalCodeController::class, 'show'])
    ->where('postalCode', '[0-9]{3,10}');

Route::get('/counties', [CountyController::class, 'showCounties']);
Route::get('/county/{name}/{letter}', [CountyController::class, 'getCitiesByLetter']);
Route::get('/user', function (Request $request) { });


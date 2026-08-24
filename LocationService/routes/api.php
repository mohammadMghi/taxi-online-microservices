<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/v1/locations/validation', [App\Http\Controllers\LocationController::class, 'handle']);
Route::post('/v1/locations/driver_current', [App\Http\Controllers\UpdateDriverLocationController::class, 'updateLocation']);
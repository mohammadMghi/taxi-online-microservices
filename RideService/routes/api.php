<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/v1/rides/take', [App\Http\Controllers\RideRequestController::class, 'handle']);
Route::post('/v1/rides/{id}/accept', [App\Http\Controllers\AccpetRideController::class, 'accept']);
Route::post('/v1/rides/{id}/reject', [App\Http\Controllers\RejectRideController::class, 'reject']);


Route::get('/v1/rides', [App\Http\Controllers\RideListController::class, 'index']);

Route::post('/v1/rides/{id}/complete', [App\Http\Controllers\RideCompletedController::class, 'handle']);
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/v1/auth/signup', [\App\Http\Controllers\SignupController::class, 'signup']);
Route::post('/v1/auth/signin', [\App\Http\Controllers\SigninController::class, 'signin']);

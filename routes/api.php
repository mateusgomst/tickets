<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth', [AuthController::class, 'login'])->name('api.auth');

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::get('/me', function (Request $request) {
        return $request->user();
    })->name('api.me');
});

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
})->name('api.test');

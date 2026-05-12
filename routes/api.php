<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth', [AuthController::class, 'login'])->name('api.auth');

Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
})->name('api.test');

Route::middleware('auth:api')->get('/me', function (Request $request) {
    return $request->user();
});

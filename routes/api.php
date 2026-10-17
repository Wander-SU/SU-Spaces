<?php

use App\Http\Controllers\Api\Admin\BuildingController as AdminBuildingController;
use App\Http\Controllers\Api\Admin\OverviewController;
use App\Http\Controllers\Api\Admin\RoomController as AdminRoomController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BuildingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\RoomAvailabilityController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Http\Request;

// Default
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ---- Public (read-only) ----
Route::get('/home', [HomeController::class, 'index']);//✅
Route::get('/buildings', [BuildingController::class, 'index']);//✅

// Keep "availability" ABOVE "{room}" so it is not treated as an id.
Route::get('/rooms/availability', [RoomAvailabilityController::class, 'index']);//✅
Route::get('/rooms/{room}', [RoomController::class, 'show'])->whereNumber('room');//✅
Route::get('/rooms/{room}/schedule', [RoomController::class, 'schedule'])->whereNumber('room');//✅

// ---- Auth ----
Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:5,1');//✅
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');//✅

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);//✅

    // ---- Admin ----
    Route::middleware(EnsureAdmin::class)->prefix('admin')->group(function () {
        Route::get('/overview', [OverviewController::class, 'index']);//✅
        Route::post('/phases/{phase}/buildings', [AdminBuildingController::class, 'store']);//✅
        Route::post('/buildings/{building}/rooms', [AdminRoomController::class, 'store']);//✅
    });
});
<?php

use App\Http\Controllers\BaseBookingController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('layouts.admin');
})->name('default');

// Resource Routes
Route::resource('/baseBookings',BaseBookingController::class);
Route::resource('/userManagement',UserController::class);
Route::resource('/bookings',BookingController::class);

// Update Timetable Route
Route::post('/baseBookings/updateFull',[BaseBookingController::class,'updateFull'])->name('baseBookings.updateFull');

<?php

use App\Http\Controllers\AccomodationsController;
use App\Http\Controllers\BookingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/V1/accomodations', [AccomodationsController::class, 'getAccomodations']);
Route::get('/V1/accomodation/{id}', [AccomodationsController::class, 'get_accomodation_by_id']);
Route::get('/V1/bookings', [BookingsController::class, 'get_bookings']);
Route::post('/V1/accomodation', [AccomodationsController::class, 'store']);
Route::put('/V1/accomodation/{id}', [AccomodationsController::class, 'update']);

//Rutas Bookings
Route::get('/V1/bookings', [BookingsController::class, 'get_bookings']);
Route::patch('/V1/status_booking/{id}', [BookingsController::class, 'update_status']);
Route::post('/V1/booking', [BookingsController::class, 'store']);
Route::get('/V1/bookings/calendar/{id_accomodation}', [BookingsController::class, 'calendar_accomodation_bookings']);
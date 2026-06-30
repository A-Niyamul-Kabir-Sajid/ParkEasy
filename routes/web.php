<?php

use App\Http\Controllers\LotOwner\ParkingLotController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get(
    '/owner/parking-lots/create',
    [ParkingLotController::class, 'create']
)->middleware('auth');

Route::post(
    '/owner/parking-lots',
    [ParkingLotController::class, 'store']
)->middleware('auth');

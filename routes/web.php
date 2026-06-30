<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\LotOwner\ParkingLotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardRedirectController::class)->name('dashboard');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
    });

    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'owner'])->name('dashboard');
        Route::get('/parking-lots/create', [ParkingLotController::class, 'create']);
        Route::post('/parking-lots', [ParkingLotController::class, 'store']);
    });

    Route::middleware('role:driver')->prefix('driver')->name('driver.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'driver'])->name('dashboard');
    });
});

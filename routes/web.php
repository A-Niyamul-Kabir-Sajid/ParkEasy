<?php

use App\Http\Controllers\Admin\ParkingLotVerificationController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Browse\ParkingLotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\LotOwner\ParkingLotController as LotOwnerParkingLotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/parking-lots', [ParkingLotController::class, 'index'])->name('parking-lots.browse');
Route::get('/parking-lots/{parking_lot}', [ParkingLotController::class, 'show'])->name('parking-lots.show');

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
        Route::get('/verification', [ParkingLotVerificationController::class, 'index'])->name('verification.index');
        Route::post('/verification/{parking_lot}/approve', [ParkingLotVerificationController::class, 'approve'])->name('verification.approve');
        Route::post('/verification/{parking_lot}/reject', [ParkingLotVerificationController::class, 'reject'])->name('verification.reject');
    });

    Route::middleware('role:owner')->prefix('owner')->name('owner.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'owner'])->name('dashboard');
        Route::get('/parking-lots', [LotOwnerParkingLotController::class, 'index'])->name('parking-lots.index');
        Route::get('/parking-lots/create', [LotOwnerParkingLotController::class, 'create'])->name('parking-lots.create');
        Route::post('/parking-lots', [LotOwnerParkingLotController::class, 'store'])->name('parking-lots.store');
        Route::get('/parking-lots/{parking_lot}/edit', [LotOwnerParkingLotController::class, 'edit'])->name('parking-lots.edit');
        Route::put('/parking-lots/{parking_lot}', [LotOwnerParkingLotController::class, 'update'])->name('parking-lots.update');
        Route::delete('/parking-lots/{parking_lot}', [LotOwnerParkingLotController::class, 'destroy'])->name('parking-lots.destroy');
    });

    Route::middleware('role:driver')->prefix('driver')->name('driver.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'driver'])->name('dashboard');
    });
});

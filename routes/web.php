<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\Repair\DashboardController as RepairDashboardController;

require __DIR__.'/auth.php';

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // === Repair Dashboard (ของจริง) ===
    Route::get('/repair/dashboard', [RepairDashboardController::class, 'index'])
        ->name('repair.dashboard');

    // === คง URL เดิมไว้: /dashboard → redirect ไปของจริง ===
    Route::get('/dashboard', fn () => redirect()->route('repair.dashboard'))
        ->name('dashboard');

    // Maintenance Requests
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/requests', [MaintenanceRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [MaintenanceRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [MaintenanceRequestController::class, 'store'])->name('requests.store');

        Route::get('/requests/{request}', [MaintenanceRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{request}/edit', [MaintenanceRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{request}', [MaintenanceRequestController::class, 'update'])->name('requests.update');
        Route::delete('/requests/{request}', [MaintenanceRequestController::class, 'destroy'])->name('requests.destroy');
    });
});

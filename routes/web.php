<?php

use Illuminate\Support\Facades\Route;

// Auth / Profile
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;

// App Modules
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\Repair\DashboardController as RepairDashboardController;

require __DIR__.'/auth.php';

// หน้าแรก → ไปหน้า login
Route::redirect('/', '/login');

// =====================
// Guest-only routes
// =====================
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// =====================
// Authenticated routes
// =====================
Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // ===== Repair Dashboard (Light) =====
    Route::get('/repair/dashboard', [RepairDashboardController::class, 'index'])
        ->name('repair.dashboard');

    // คง URL เดิมไว้: /dashboard → redirect ไปของจริง
    Route::get('/dashboard', fn () => redirect()->route('repair.dashboard'))
        ->name('dashboard');

    // ===== Maintenance Requests =====
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::get('/requests', [MaintenanceRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/create', [MaintenanceRequestController::class, 'create'])->name('requests.create');
        Route::post('/requests', [MaintenanceRequestController::class, 'store'])->name('requests.store');

        Route::get('/requests/{request}', [MaintenanceRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{request}/edit', [MaintenanceRequestController::class, 'edit'])->name('requests.edit');
        Route::put('/requests/{request}', [MaintenanceRequestController::class, 'update'])->name('requests.update');
        Route::delete('/requests/{request}', [MaintenanceRequestController::class, 'destroy'])->name('requests.destroy');
    });

    // ====== Assets (placeholder ให้เมนูไม่พัง) ======
    Route::prefix('assets')->name('assets.')->group(function () {
        // แค่ให้มีชื่อ route ก่อน คลิกแล้วค่อยไปทำ view/controller ต่อภายหลัง
        Route::view('/', 'assets.index')->name('index');     // resources/views/assets/index.blade.php (ทำภายหลังได้)
        // ถ้าจะทำเต็มภายหลัง: Route::resource('/', AssetController::class)->names('assets');
    });

    // ====== Users (placeholder ให้เมนูไม่พัง) ======
    Route::prefix('users')->name('users.')->group(function () {
        Route::view('/', 'users.index')->name('index');      // resources/views/users/index.blade.php (ทำภายหลังได้)
        // ภายหลังจะเปลี่ยนเป็น Route::resource('users', UserController::class)->names('users');
    });
});

// (optional) fallback
// Route::fallback(fn () => redirect()->route('repair.dashboard'));

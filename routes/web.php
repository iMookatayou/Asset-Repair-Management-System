<?php

use Illuminate\Support\Facades\Route;

// Auth / Profile
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;

// App Modules (Web)
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\MaintenanceLogController;
use App\Http\Controllers\Repair\DashboardController as RepairDashboardController;

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| - หน้าเว็บ (Blade) ทั้งหมดอยู่ที่นี่
| - API แยกไปที่ routes/api.php แล้ว
*/

Route::redirect('/', '/login');

// ---------------------
// Guest-only
// ---------------------
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// ---------------------
// Authenticated
// ---------------------
Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Dashboard (ซ่อนของเดิม ชี้เข้า Repair Dashboard)
    Route::get('/repair/dashboard', [RepairDashboardController::class, 'index'])->name('repair.dashboard');
    Route::get('/dashboard', fn () => redirect()->route('repair.dashboard'))->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Maintenance Requests (WEB)
    |--------------------------------------------------------------------------
    | NOTE:
    | - ชื่อ route ต้องตรงกับที่ Blade ใช้: maintenance.requests.*
    | - Controller method ใช้เมธอดมาตรฐาน + 2 เมธอดพิเศษสำหรับหน้าเว็บ
    |     - transition()         : โพสต์เปลี่ยนสถานะจากฟอร์ม
    |     - AttachmentController@storeForRequest : อัปโหลดไฟล์จากฟอร์ม
    */
    Route::prefix('maintenance')->name('maintenance.')->group(function () {
        Route::prefix('requests')->name('requests.')->group(function () {

            // หน้าเว็บ (Blade)
            Route::get('/',        [MaintenanceRequestController::class, 'indexPage'])->name('index');
            Route::get('/create',  [MaintenanceRequestController::class, 'createPage'])->name('create');
            Route::get('/{req}',   [MaintenanceRequestController::class, 'showPage'])->name('show');

            // submit จากฟอร์ม create
            Route::post('/', [MaintenanceRequestController::class, 'store'])->name('store');

            // เปลี่ยนสถานะ & อัปโหลดไฟล์จากหน้าเว็บ
            Route::post('/{req}/transition',  [MaintenanceRequestController::class, 'transitionFromBlade'])->name('transition');
            Route::post('/{req}/attachments', [MaintenanceRequestController::class, 'uploadAttachmentFromBlade'])->name('attachments');

            // (ถ้าจะมีหน้า logs บนเว็บ)
            Route::get('/{req}/logs', [MaintenanceLogController::class, 'index'])->name('logs');
        });
    });

    // (ทางเลือก) เมนูอื่น ๆ ให้มีปลายทางก่อน จะค่อยทำภายหลังก็ได้
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::view('/', 'assets.index')->name('index'); // สร้างไฟล์ resources/views/assets/index.blade.php ไว้ก่อน
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::view('/', 'users.index')->name('index');   // สร้างไฟล์ resources/views/users/index.blade.php ไว้ก่อน
    });
});

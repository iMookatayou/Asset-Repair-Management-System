<?php

use Illuminate\Support\Facades\Route;

// Auth / Profile
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\PasswordController;

// App Modules (Web)
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\Repair\DashboardController as RepairDashboardController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\MaintenanceOperationLogController;
use App\Http\Controllers\MaintenanceAssignmentController;
use App\Http\Controllers\MaintenanceRatingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

// login
Route::redirect('/', '/login');

// Guest-only
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Auth-only
Route::middleware(['auth'])->group(function () {

    // Debug
    Route::get('/debug/whoami', function (Request $request) {
        /** @var \App\Models\User|null $u */
        $u = $request->user();

        return response()->json([
            'id'    => $u?->id,
            'email' => $u?->email,
            'role'  => $u?->role,
            'can_manage_users' => $u ? Gate::forUser($u)->allows('manage-users') : false,
            'guard' => Auth::getDefaultDriver(),
        ]);
    });

    // Dashboard
    Route::get('/repair/dashboard', [RepairDashboardController::class, 'index'])->name('repair.dashboard');
    Route::get('/dashboard', fn () => redirect()->route('repair.dashboard'))->name('dashboard');

    // Maintenance
    Route::prefix('maintenance')->name('maintenance.')->group(function () {

        Route::prefix('requests')->name('requests.')->group(function () {

            Route::get('/', [MaintenanceRequestController::class, 'indexPage'])->name('index');
            Route::get('/create', [MaintenanceRequestController::class, 'createPage'])->name('create');
            Route::post('/', [MaintenanceRequestController::class, 'store'])->name('store');

            Route::get('/{maintenanceRequest}', [MaintenanceRequestController::class, 'showPage'])->name('show');
            Route::get('/{maintenanceRequest}/edit', [MaintenanceRequestController::class, 'edit'])->name('edit');
            Route::put('/{maintenanceRequest}', [MaintenanceRequestController::class, 'update'])->name('update');

            Route::post('/{req}/accept', [MaintenanceRequestController::class, 'acceptJobQuick'])
                ->name('accept');

            // Work Order
            Route::get('/{maintenanceRequest}/work-order', [MaintenanceRequestController::class, 'printWorkOrder'])
                ->name('work-order');

            // Operation Log
            Route::post('/{maintenanceRequest}/operation-log', [MaintenanceOperationLogController::class, 'upsert'])
                ->name('operation-log');

            // Attachments
            Route::post('/{maintenanceRequest}/attachments', [MaintenanceRequestController::class, 'uploadAttachmentFromBlade'])
                ->name('attachments');

            Route::delete('/{maintenanceRequest}/attachments/{attachment}', [MaintenanceRequestController::class, 'destroyAttachment'])
                ->name('attachments.destroy');

            // Assignments
            Route::post('/{maintenanceRequest}/assignments', [MaintenanceAssignmentController::class, 'store'])
                ->name('assignments.store');

            Route::delete('/assignments/{assignment}', [MaintenanceAssignmentController::class, 'destroy'])
                ->name('assignments.destroy');

            Route::prefix('rating')->name('rating.')->group(function () {
                Route::get('/evaluate', [MaintenanceRatingController::class, 'evaluateList'])->name('evaluate');
                Route::get('/technicians', [MaintenanceRatingController::class, 'technicianDashboard'])->name('technicians');
            });
        });
    });

    // Repair views
    Route::get('/repair/my-jobs', [MaintenanceRequestController::class, 'myJobsPage'])->name('repairs.my_jobs');
    Route::get('/repair/queue',   [MaintenanceRequestController::class, 'queuePage'])->name('repairs.queue');

    Route::post('/repair/accept/{req}', [MaintenanceRequestController::class, 'acceptCase'])
        ->name('repairs.accept');

    Route::post('/repair/reject/{req}', [MaintenanceRequestController::class, 'rejectCase'])
        ->name('repairs.reject');

    Route::post('/repair/cancel/{req}', [MaintenanceRequestController::class, 'cancelCase'])
        ->name('repairs.cancel');

    // Attachments (serve private files after auth)
    Route::get('/attachments/{attachment}', [AttachmentController::class, 'show'])->name('attachments.show');

    // Chat
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/threads', [ChatController::class, 'storeThread'])->name('chat.store');
    Route::get('/chat/threads/{thread}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/threads/{thread}/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/threads/{thread}/messages', [ChatController::class, 'storeMessage'])->name('chat.messages.store');
    Route::get('/chat/my-updates', [ChatController::class, 'myUpdates'])->name('chat.my_updates');
    Route::post('/chat/threads/{thread}/lock',   [ChatController::class, 'lock'])->name('chat.lock');
    Route::post('/chat/threads/{thread}/unlock', [ChatController::class, 'unlock'])->name('chat.unlock');

    // Assets
    Route::get('/assets',                 [AssetController::class,'indexPage'])->name('assets.index');
    Route::get('/assets/create',          [AssetController::class,'createPage'])->name('assets.create');
    Route::post('/assets',                [AssetController::class,'storePage'])->name('assets.store');
    Route::get('/assets/{asset}',         [AssetController::class,'showPage'])->name('assets.show');
    Route::get('/assets/{asset}/edit',    [AssetController::class,'editPage'])->name('assets.edit');
    Route::put('/assets/{asset}',         [AssetController::class,'updatePage'])->name('assets.update');
    Route::delete('/assets/{asset}',      [AssetController::class,'destroyPage'])->name('assets.destroy');
    Route::get('/assets/{asset}/print',   [AssetController::class,'printPage'])->name('assets.print');

    // Admin - Users
    Route::prefix('admin')->name('admin.')->middleware('can:manage-users')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',            [AdminUserController::class, 'index'])->name('index');
            Route::get('/create',      [AdminUserController::class, 'create'])->name('create');
            Route::post('/',           [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}',      [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}',   [AdminUserController::class, 'destroy'])->name('destroy');
            Route::post('/bulk',       [AdminUserController::class, 'bulk'])->name('bulk');
        });
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',    [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',   [ProfileController::class, 'destroy'])->name('profile.destroy');

    // เปลี่ยนรหัสผ่าน (ให้ทุกคนทำเองได้) ต้องแก้
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
});

// Auth scaffolding routes
require __DIR__ . '/auth.php';

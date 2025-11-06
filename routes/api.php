<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AssetController,
    MaintenanceRequestController,
    AttachmentController,
    MaintenanceLogController
};
use App\Http\Controllers\Api\ChatController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $r) => $r->user());

    Route::name('api.')->group(function () {
      Route::apiResource('assets', AssetController::class);
  });
      
    Route::prefix('repair-requests')->name('repair-requests.')->group(function () {
        Route::get('/',                  [MaintenanceRequestController::class, 'index'])->name('index');
        Route::post('/',                 [MaintenanceRequestController::class, 'store'])->name('store');
        Route::get('/{req}',             [MaintenanceRequestController::class, 'show'])->name('show');
        Route::put('/{req}',             [MaintenanceRequestController::class, 'update'])->name('update');
        Route::post('/{req}/transition', [MaintenanceRequestController::class, 'transition'])->name('transition');
        Route::get('/{req}/logs',        [MaintenanceLogController::class, 'index'])->name('logs');
    });

    Route::prefix('api/assets')->name('api.assets.')->group(function () {
        Route::get('/', [AssetController::class, 'index'])->name('index');
        Route::post('/', [AssetController::class, 'store'])->name('store');
        Route::get('/{asset}', [AssetController::class, 'show'])->name('show');
        Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
    });

    Route::post('/attachments',                [AttachmentController::class, 'store'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('attachments.destroy');

    Route::get('/threads',            [ChatController::class, 'index'])->name('threads.index');
    Route::post('/threads',           [ChatController::class, 'store'])->name('threads.store');
    Route::get('/threads/{thread}',   [ChatController::class, 'show'])->name('threads.show');

    // Messages
    Route::get('/threads/{thread}/messages',  [ChatController::class, 'messages'])->name('messages.index');
    Route::post('/threads/{thread}/messages', [ChatController::class, 'storeMessage'])->name('messages.store');
});

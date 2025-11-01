<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AssetController, MaintenanceRequestController, AttachmentController, MaintenanceLogController};

Route::middleware('auth:sanctum')->group(function () {
    
  //Userinfo
  Route::get('/user', fn(Request $r) => $r->user());

  // Assets
  Route::get('/assets', [AssetController::class, 'index']);
  Route::post('/assets', [AssetController::class, 'store']);
  Route::get('/assets/{asset}', [AssetController::class, 'show']);
  Route::put('/assets/{asset}', [AssetController::class, 'update']);

  // Maintenance Requests
  Route::prefix('repair-requests')->name('repair-requests.')->group(function () {
    Route::get('/',          [MaintenanceRequestController::class, 'index'])->name('index');
    Route::post('/',         [MaintenanceRequestController::class, 'store'])->name('store');
    Route::get('/{req}',     [MaintenanceRequestController::class, 'show'])->name('show');
    Route::put('/{req}',     [MaintenanceRequestController::class, 'update'])->name('update');
    Route::post('/{req}/transition', [MaintenanceRequestController::class, 'transition'])->name('transition');
    Route::get('/{req}/logs', [MaintenanceLogController::class, 'index'])->name('logs');
  });

  
  // Attachments
  Route::post('/attachments', [AttachmentController::class, 'store']);  
  Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});


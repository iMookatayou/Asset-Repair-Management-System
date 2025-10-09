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
  Route::get('/repair-requests', [MaintenanceRequestController::class, 'index']);
  Route::post('/repair-requests', [MaintenanceRequestController::class, 'store']);
  Route::get('/repair-requests/{req}', [MaintenanceRequestController::class, 'show']);
  Route::put('/repair-requests/{req}', [MaintenanceRequestController::class, 'update']);

  // Transitions (state machine)
  Route::post('/repair-requests/{req}/transition', [MaintenanceRequestController::class, 'transition']);
  // Logs (optional read)
  Route::get('/repair-requests/{req}/logs', [MaintenanceLogController::class, 'index']);

  // Attachments
  Route::post('/attachments', [AttachmentController::class, 'store']);  
  Route::delete('/attachments/{attachment}', [AttachmentController::class, 'destroy']);
});

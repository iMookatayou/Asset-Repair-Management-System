<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\MaintenanceLogController;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('assets', AssetController::class);
    Route::apiResource('maintenance-requests', MaintenanceRequestController::class);
    Route::apiResource('attachments', AttachmentController::class)->only(['store','destroy','index','show']);
    Route::apiResource('maintenance-logs', MaintenanceLogController::class)->only(['index','store','show']);

    Route::post('maintenance-requests/{id}/assign', [MaintenanceRequestController::class, 'assign']);
    Route::post('maintenance-requests/{id}/complete', [MaintenanceRequestController::class, 'complete']);
});

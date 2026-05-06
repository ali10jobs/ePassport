<?php

use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OpenApiController;
use App\Http\Controllers\Api\V1\WorkerCertificationController;
use App\Http\Controllers\Api\V1\WorkerController;
use App\Http\Controllers\Api\V1\WorkerMedicalRecordController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/

// Public endpoints (no auth)
Route::get('/health', [HealthController::class, 'check'])->name('health');
Route::get('/openapi.json', [OpenApiController::class, 'spec'])->name('openapi');

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [HealthController::class, 'me'])->name('me');

    // Workers
    Route::prefix('workers')->name('workers.')->group(function () {
        Route::get('/', [WorkerController::class, 'index'])->name('index');
        Route::post('/', [WorkerController::class, 'store'])->name('store');
        Route::get('/{worker}', [WorkerController::class, 'show'])->name('show');
        Route::patch('/{worker}', [WorkerController::class, 'update'])->name('update');
        Route::delete('/{worker}', [WorkerController::class, 'destroy'])->name('destroy');
        Route::get('/{worker}/passport', [WorkerController::class, 'passport'])->name('passport');
        Route::get('/{worker}/qr/helmet', [WorkerController::class, 'helmetQr'])->name('qr.helmet');
        Route::get('/{worker}/qr/coverall', [WorkerController::class, 'coverallQr'])->name('qr.coverall');

        // Worker certifications
        Route::get('/{worker}/certifications', [WorkerCertificationController::class, 'index'])->name('certifications.index');
        Route::post('/{worker}/certifications', [WorkerCertificationController::class, 'store'])->name('certifications.store');
        Route::delete('/{worker}/certifications/{certificationId}', [WorkerCertificationController::class, 'destroy'])->name('certifications.destroy');

        // Worker medical records
        Route::get('/{worker}/medical-records', [WorkerMedicalRecordController::class, 'index'])->name('medical.index');
        Route::post('/{worker}/medical-records', [WorkerMedicalRecordController::class, 'store'])->name('medical.store');
    });
});

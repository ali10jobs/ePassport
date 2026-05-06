<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EquipmentController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\OpenApiController;
use App\Http\Controllers\Api\V1\PermitController;
use App\Http\Controllers\Api\V1\ScanController;
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
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('/me', [HealthController::class, 'me'])->name('me');

    // Workers
    Route::prefix('workers')->name('workers.')->group(function () {
        Route::post('/bulk', [WorkerController::class, 'bulkImport'])->name('bulk');
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

    // Scans (gate verification)
    Route::prefix('scans')->name('scans.')->group(function () {
        Route::get('/', [ScanController::class, 'index'])->name('index');
        Route::post('/verify', [ScanController::class, 'verify'])->name('verify');
        Route::post('/verify-pair', [ScanController::class, 'verifyPair'])->name('verify_pair');
        Route::post('/verify-equipment-operator', [ScanController::class, 'verifyEquipmentOperator'])->name('verify_eq_op');
    });

    // Equipment
    Route::prefix('equipment')->name('equipment.')->group(function () {
        Route::post('/bulk', [EquipmentController::class, 'bulkImport'])->name('bulk');
        Route::get('/', [EquipmentController::class, 'index'])->name('index');
        Route::post('/', [EquipmentController::class, 'store'])->name('store');
        Route::get('/{equipment}', [EquipmentController::class, 'show'])->name('show');
        Route::patch('/{equipment}', [EquipmentController::class, 'update'])->name('update');
        Route::delete('/{equipment}', [EquipmentController::class, 'destroy'])->name('destroy');
        Route::get('/{equipment}/qr', [EquipmentController::class, 'qr'])->name('qr');
        Route::post('/{equipment}/certifications', [EquipmentController::class, 'attachCertification'])->name('certifications.store');
        Route::post('/{equipment}/operators', [EquipmentController::class, 'pairOperator'])->name('operators.pair');
    });

    // Permits
    Route::prefix('permits')->name('permits.')->group(function () {
        Route::get('/', [PermitController::class, 'index'])->name('index');
        Route::post('/', [PermitController::class, 'store'])->name('store');
        Route::get('/{permit}', [PermitController::class, 'show'])->name('show');
        Route::post('/{permit}/workers', [PermitController::class, 'attachWorkers'])->name('workers.attach');
        Route::post('/{permit}/equipment', [PermitController::class, 'attachEquipment'])->name('equipment.attach');
    });
});

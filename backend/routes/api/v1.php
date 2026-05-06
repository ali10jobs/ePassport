<?php

use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| Mounted at /api/v1/*. Resource controllers register here as features land.
| Authentication: cookie-session (web SPA via Sanctum), bearer token (mobile +
| ERPs via Sanctum personal access tokens).
|
| Routes group:
|   - public (no auth): /api/v1/hazard-reports/anonymous, /api/v1/health
|   - auth required:    everything else, via auth:sanctum middleware
|
*/

// Public endpoints (no auth)
Route::get('/health', [HealthController::class, 'check'])->name('health');

// Authenticated endpoints
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [HealthController::class, 'me'])->name('me');
    // Resource routes register here per phase: workers, equipment, scans, permits, etc.
});
